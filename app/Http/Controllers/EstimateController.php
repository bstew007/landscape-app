<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Estimate;
use App\Models\Property;
use App\Models\SiteVisit;
use App\Models\Invoice;
use App\Mail\EstimateMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EstimateController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $clientId = $request->get('client_id');

        $estimates = Estimate::with(['client', 'property'])
            ->when($status && in_array($status, Estimate::STATUSES, true), fn ($q) => $q->where('status', $status))
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $clients = Client::orderBy('company_name')->orderBy('last_name')->get();

        return view('estimates.index', compact('estimates', 'status', 'clientId', 'clients'));
    }

    public function create(Request $request)
    {
        $estimate = new Estimate([
            'client_id' => $request->get('client_id'),
            'property_id' => $request->get('property_id'),
            'site_visit_id' => $request->get('site_visit_id'),
        ]);

        if ($estimate->site_visit_id) {
            $estimate->line_items = $this->buildLineItemsFromSiteVisit($estimate->site_visit_id);
        }

        return view('estimates.create', $this->formData($estimate));
    }

    public function store(Request $request)
    {
        $data = $this->validateEstimate($request);

        $hasLineItems = ! empty($data['line_items']);

        if (! $hasLineItems && ! empty($data['site_visit_id'])) {
            $data['line_items'] = $this->buildLineItemsFromSiteVisit($data['site_visit_id']);
        }

        $estimate = Estimate::create($data);

        return redirect()->route('estimates.show', $estimate)->with('success', 'Estimate created.');
    }

    public function show(Estimate $estimate)
    {
        $estimate->load(['client', 'property', 'siteVisit', 'invoice', 'emailSender']);
        return view('estimates.show', compact('estimate'));
    }

    public function edit(Estimate $estimate)
    {
        return view('estimates.edit', $this->formData($estimate));
    }

    public function update(Request $request, Estimate $estimate)
    {
        $data = $this->validateEstimate($request);

        if (empty($data['line_items']) && ! empty($data['site_visit_id'])) {
            $data['line_items'] = $this->buildLineItemsFromSiteVisit($data['site_visit_id']);
        }

        $estimate->update($data);

        return redirect()->route('estimates.show', $estimate)->with('success', 'Estimate updated.');
    }

    public function destroy(Estimate $estimate)
    {
        $estimate->delete();

        return redirect()->route('estimates.index')->with('success', 'Estimate deleted.');
    }

    protected function validateEstimate(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'client_id' => 'required|exists:clients,id',
            'property_id' => 'nullable|exists:properties,id',
            'site_visit_id' => 'nullable|exists:site_visits,id',
            'status' => 'required|in:' . implode(',', Estimate::STATUSES),
            'total' => 'nullable|numeric',
            'expires_at' => 'nullable|date',
            'line_items' => 'nullable|string',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        $decoded = $this->decodeLineItems($data['line_items'] ?? null);
        $data['line_items'] = $decoded;

        return $data;
    }

    protected function formData(Estimate $estimate): array
    {
        $clients = Client::with('properties')->orderBy('company_name')->orderBy('last_name')->get();
        $siteVisits = SiteVisit::with('client')->latest()->limit(50)->get();

        return [
            'estimate' => $estimate,
            'clients' => $clients,
            'siteVisits' => $siteVisits,
            'statuses' => Estimate::STATUSES,
        ];
    }

    public function previewEmail(Estimate $estimate)
    {
        $html = (new EstimateMail($estimate))->render();

        return view('estimates.preview-email', compact('estimate', 'html'));
    }

    public function sendEmail(Estimate $estimate)
    {
        Mail::to($estimate->client->email ?? 'test@example.com')->send(new EstimateMail($estimate));

        $now = now();

        $estimate->forceFill([
            'email_sent_at' => $estimate->email_sent_at ?? $now,
            'email_last_sent_at' => $now,
            'email_send_count' => (int) ($estimate->email_send_count ?? 0) + 1,
            'email_last_sent_by' => auth()->id(),
        ])->save();

        $estimate->refresh();

        $message = $estimate->email_send_count > 1
            ? "Estimate #{$estimate->id} re-sent to {$estimate->client->email}."
            : "Estimate #{$estimate->id} emailed to {$estimate->client->email}.";

        return back()->with('success', $message);
    }

    public function createInvoice(Estimate $estimate)
    {
        $path = 'invoices/estimate-' . $estimate->id . '.txt';
        $content = "Invoice for Estimate #{$estimate->id}\nTotal: $" . number_format($estimate->total ?? 0, 2);
        Storage::disk('public')->put($path, $content);

        $invoice = Invoice::updateOrCreate(
            ['estimate_id' => $estimate->id],
            [
                'status' => 'issued',
                'amount' => $estimate->total,
                'due_date' => now()->addDays(30),
                'pdf_path' => $path,
            ]
        );

        return back()->with('success', "Invoice #{$invoice->id} generated and stored.");
    }

    public function print(Estimate $estimate)
    {
        return view('estimates.print', compact('estimate'));
    }

    public function siteVisitLineItems(SiteVisit $siteVisit): JsonResponse
    {
        $lineItems = $this->buildLineItemsFromSiteVisit($siteVisit->id) ?? [];

        return response()->json([
            'line_items' => $lineItems,
            'client_id' => $siteVisit->client_id,
            'property_id' => $siteVisit->property_id,
        ]);
    }

    protected function buildLineItemsFromSiteVisit(?int $siteVisitId): ?array
    {
        if (! $siteVisitId) {
            return null;
        }

        $siteVisit = SiteVisit::with(['calculations'])->find($siteVisitId);

        if (! $siteVisit || $siteVisit->calculations->isEmpty()) {
            return null;
        }

        return $siteVisit->calculations->map(function ($calculation) {
            $data = $calculation->data ?? [];
            $total = (float) ($data['final_price'] ?? $data['price'] ?? $data['total'] ?? 0);
            $labor = (float) ($data['labor_cost'] ?? 0);
            $materials = (float) ($data['material_total'] ?? 0);

            if ($total === 0 && ($labor + $materials) > 0) {
                $total = $labor + $materials;
            }

            return [
                'label' => ucwords(str_replace('_', ' ', $calculation->calculation_type)),
                'qty' => 1,
                'rate' => $total,
                'total' => $total,
                'details' => [
                    'labor' => $labor,
                    'materials' => $materials,
                ],
            ];
        })->toArray();
    }

    protected function decodeLineItems(?string $payload): ?array
    {
        if (! $payload) {
            return null;
        }

        $decoded = json_decode($payload, true);

        return is_array($decoded) ? $decoded : null;
    }
}
