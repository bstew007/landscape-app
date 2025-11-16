<?php

namespace App\Http\Controllers;

use App\Mail\EstimateMail;
use App\Models\Calculation;
use App\Models\Client;
use App\Models\Estimate;
use App\Models\Invoice;
use App\Models\LaborItem;
use App\Models\Material;
use App\Models\Property;
use App\Models\SiteVisit;
use App\Services\CalculationImportService;
use App\Services\EstimateItemService;
use App\Support\ScopeDescriptionResolver;
use App\Support\ScopeSummaryBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EstimateController extends Controller
{
    public function __construct(
        protected EstimateItemService $itemService,
        protected CalculationImportService $calculationImporter
    )
    {
    }

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

        return view('estimates.create', $this->formData($estimate));
    }

    public function store(Request $request)
    {
        $data = $this->validateEstimate($request);

        $hasLineItems = ! empty($data['line_items']);

        if (! $hasLineItems && ! empty($data['site_visit_id'])) {
            $data['line_items'] = $this->buildLineItemsFromSiteVisit($data['site_visit_id']);
        }

        $lineItems = $data['site_visit_id']
            ? $this->buildLineItemsFromSiteVisit($data['site_visit_id'])
            : null;

        try {
            $this->assertMinimumProfit($lineItems);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $estimate = Estimate::create($data);

        // Auto-create a default General work area
        if ($estimate->areas()->count() === 0) {
            $estimate->areas()->create(['name' => 'General', 'sort_order' => 1]);
        }

        if ($data['site_visit_id']) {
            // Prefer structured import from calculations so budget margins apply per line item
            $siteVisit = SiteVisit::with('calculations')->find($data['site_visit_id']);
            if ($siteVisit) {
                $this->calculationImporter->importSiteVisitCalculations($estimate->fresh(), $siteVisit, true);
            } else {
                $this->itemService->recalculateTotals($estimate);
            }
        } elseif ($lineItems) {
            // Legacy path if provided explicitly
            $this->itemService->syncFromLegacyLineItems($estimate, $lineItems);
        } else {
            $this->itemService->recalculateTotals($estimate);
        }

        return redirect()->route('estimates.show', $estimate)->with('success', 'Estimate created.');
    }

    public function show(Estimate $estimate)
    {
        $estimate->load([
            'client',
            'property',
            'siteVisit.calculations',
            'invoice',
            'emailSender',
            'items.calculation',
            'areas',
            'files',
        ]);

        $materials = Material::where('is_active', true)->orderBy('name')->get();
        $laborCatalog = LaborItem::where('is_active', true)->orderBy('name')->get();

        // Budget default margin for UI defaults
        $budget = app(\App\Services\BudgetService::class)->active();
        $defaultMarginRate = (float) (($budget->desired_profit_margin ?? 0.2));
        $defaultMarginPercent = round($defaultMarginRate * 100, 1);

        $itemsByType = $estimate->items->groupBy('item_type');
        $typeBreakdown = [
            'material' => [
                'label' => 'Materials',
                'revenue' => $estimate->material_subtotal,
                'cost' => $estimate->material_cost_total,
                'profit' => $itemsByType->get('material', collect())->sum('margin_total'),
            ],
            'labor' => [
                'label' => 'Labor',
                'revenue' => $estimate->labor_subtotal,
                'cost' => $estimate->labor_cost_total,
                'profit' => $itemsByType->get('labor', collect())->sum('margin_total'),
            ],
            'fee' => [
                'label' => 'Fees',
                'revenue' => $estimate->fee_total,
                'cost' => $itemsByType->get('fee', collect())->sum('cost_total'),
                'profit' => $itemsByType->get('fee', collect())->sum('margin_total'),
            ],
            'discount' => [
                'label' => 'Discounts',
                'revenue' => $estimate->discount_total,
                'cost' => $itemsByType->get('discount', collect())->sum('cost_total'),
                'profit' => $itemsByType->get('discount', collect())->sum('margin_total'),
            ],
        ];

        $financialSummary = [
            'revenue' => $estimate->revenue_total,
            'costs' => $estimate->cost_total,
            'gross_profit' => $estimate->profit_total,
            'net_profit' => $estimate->net_profit_total,
            'profit_margin' => $estimate->profit_margin,
            'net_margin' => $estimate->net_margin,
            'tax_total' => $estimate->tax_total,
        ];

        return view('estimates.show', [
            'estimate' => $estimate,
            'materials' => $materials,
            'laborCatalog' => $laborCatalog,
            'financialSummary' => $financialSummary,
            'typeBreakdown' => $typeBreakdown,
            'defaultMarginRate' => $defaultMarginRate,
            'defaultMarginPercent' => $defaultMarginPercent,
            'statuses' => Estimate::STATUSES,
        ]);
    }

    public function edit(Estimate $estimate)
    {
        return view('estimates.edit', $this->formData($estimate));
    }

    public function update(Request $request, Estimate $estimate)
    {
        $data = $this->validateEstimate($request);

        $lineItems = $data['site_visit_id']
            ? $this->buildLineItemsFromSiteVisit($data['site_visit_id'])
            : null;

        try {
            $this->assertMinimumProfit($lineItems);
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        $estimate->update($data);
        if ($data['site_visit_id']) {
            $siteVisit = SiteVisit::with('calculations')->find($data['site_visit_id']);
            if ($siteVisit) {
                $this->calculationImporter->importSiteVisitCalculations($estimate->fresh(), $siteVisit, true);
            } else {
                $this->itemService->recalculateTotals($estimate->fresh());
            }
        } elseif ($lineItems) {
            $this->itemService->syncFromLegacyLineItems($estimate->fresh(), $lineItems);
        } else {
            $this->itemService->recalculateTotals($estimate->fresh());
        }

        return redirect()->route('estimates.show', $estimate)->with('success', 'Estimate updated.');
    }

    public function destroy(Estimate $estimate)
    {
        $estimate->delete();

        return redirect()->route('estimates.index')->with('success', 'Estimate deleted.');
    }


    public function removeCalculation(Estimate $estimate, Calculation $calculation)
    {
        if ($estimate->site_visit_id && $calculation->site_visit_id !== $estimate->site_visit_id) {
            abort(404);
        }

        $this->itemService->removeCalculationItems($estimate, $calculation->id);

        if (request()->ajax() || request()->wantsJson()) {
            $estimate->refresh();
            return response()->json([
                'status' => 'ok',
                'totals' => [
                    'material_subtotal' => $estimate->material_subtotal,
                    'labor_subtotal' => $estimate->labor_subtotal,
                    'fee_total' => $estimate->fee_total,
                    'discount_total' => $estimate->discount_total,
                    'tax_total' => $estimate->tax_total,
                    'grand_total' => $estimate->grand_total,
                ],
            ]);
        }

        return back()->with('success', 'Calculation items removed from estimate.');
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
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'terms_header' => 'nullable|string',
            'terms_footer' => 'nullable|string',
            'crew_notes' => 'nullable|string',
        ]);

        return $data;
    }

    protected function formData(Estimate $estimate): array
    {
        $clients = Client::with('properties')->orderBy('company_name')->orderBy('last_name')->get();
        $siteVisits = SiteVisit::with(['client', 'calculations'])
            ->latest()
            ->limit(50)
            ->get()
            ->map(function (SiteVisit $visit) {
                $descriptions = ScopeDescriptionResolver::descriptionsForSiteVisit($visit);
                $visit->scope_note_template = ScopeDescriptionResolver::templateFromDescriptions($descriptions);
                return $visit;
            });

        $scopeSummaries = ScopeSummaryBuilder::fromEstimate($estimate);
        $scopeDescriptions = ScopeDescriptionResolver::descriptionsForEstimate($estimate);
        $scopeNoteTemplate = ScopeDescriptionResolver::templateFromDescriptions($scopeDescriptions);

        return [
            'estimate' => $estimate,
            'clients' => $clients,
            'siteVisits' => $siteVisits,
            'statuses' => Estimate::STATUSES,
            'scopeSummaries' => $scopeSummaries,
            'scopeNoteTemplate' => $scopeNoteTemplate,
        ];
    }

    protected function assertMinimumProfit(?array $lineItems, string $context = 'saved', float $minimum = 10.0): void
    {
        $margin = $this->calculateProfitPercent($lineItems);

        if ($margin !== null && $margin < $minimum) {
            $action = $context === 'sent' ? 'sent' : 'saved';
            throw ValidationException::withMessages([
                'line_items' => "Profit margin is {$margin}% and must be at least {$minimum}% before this estimate can be {$action}.",
            ]);
        }
    }

    protected function calculateProfitPercent(?array $lineItems): ?float
    {
        if (!is_array($lineItems) || empty($lineItems)) {
            return null;
        }

        $revenue = 0;
        $cost = 0;

        foreach ($lineItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            $qty = (float) ($item['qty'] ?? 1);
            $price = (float) ($item['price'] ?? $item['rate'] ?? 0);
            $lineRevenue = $item['total'] ?? ($qty * $price);
            $lineCost = (float) ($item['cost'] ?? 0) * ($qty ?: 1);

            $revenue += max(0, $lineRevenue);
            $cost += max(0, $lineCost);
        }

        if ($revenue <= 0) {
            return null;
        }

        $profit = $revenue - $cost;

        return round(($profit / $revenue) * 100, 2);
    }

    public function previewEmail(Estimate $estimate)
    {
        $html = (new EstimateMail($estimate))->render();

        return view('estimates.preview-email', compact('estimate', 'html'));
    }

    public function sendEmail(Estimate $estimate)
    {
        try {
            $this->assertMinimumProfit($estimate->line_items, 'sent');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

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
                'amount' => ($estimate->grand_total ?? $estimate->total ?? 0),
                'due_date' => now()->addDays(30),
                'pdf_path' => $path,
            ]
        );

        return back()->with('success', "Invoice #{$invoice->id} generated and stored.");
    }

    public function print(Estimate $estimate)
    {
        $scopeSummaries = ScopeSummaryBuilder::fromEstimate($estimate);

        return view('estimates.print', [
            'estimate' => $estimate,
            'scopeSummaries' => $scopeSummaries,
        ]);
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
