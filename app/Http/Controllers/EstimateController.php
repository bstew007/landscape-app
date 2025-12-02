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
        $activeBudgetName = $budget ? $budget->name : 'No Active Budget';
        
        // Overhead recovery rate per hour - use pre-calculated value from budget
        $overheadRate = 0.0;
        if ($budget) {
            // Primary source: saved OH recovery rate (calculated and stored during budget update)
            $overheadRate = (float) data_get($budget->inputs, 'oh_recovery.labor_hour.markup_per_hour', 0);
            
            // Fallback: use outputs if inputs not available (for backwards compatibility)
            if ($overheadRate == 0 && $budget->outputs) {
                $outputs = $budget->outputs ?? [];
                $laborOutputs = $outputs['labor'] ?? [];
                $overheadRate = (float) ($laborOutputs['ohr'] ?? 0);
            }
        }
        
        // Debug: Log what we calculated
        \Log::info('EstimateController overhead rate', [
            'overhead_rate' => $overheadRate,
            'budget_id' => $budget?->id,
        ]);
        
        // Overhead recovery model info
        $overheadRecoveryModel = '—';
        if ($budget && isset($budget->inputs['oh_recovery'])) {
            $ohRecovery = $budget->inputs['oh_recovery'];
            if (($ohRecovery['labor_hours']['activated'] ?? false)) {
                $overheadRecoveryModel = 'Labor Hours';
            } elseif (($ohRecovery['revenue']['activated'] ?? false)) {
                $overheadRecoveryModel = 'Revenue (SORS)';
            } elseif (($ohRecovery['dual']['activated'] ?? false)) {
                $overheadRecoveryModel = 'Dual-Base';
            }
        }

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
            'budget' => $budget,
            'materials' => $materials,
            'laborCatalog' => $laborCatalog,
            'financialSummary' => $financialSummary,
            'typeBreakdown' => $typeBreakdown,
            'defaultMarginRate' => $defaultMarginRate,
            'defaultMarginPercent' => $defaultMarginPercent,
            'activeBudgetName' => $activeBudgetName,
            'overheadRecoveryModel' => $overheadRecoveryModel,
            'overheadRate' => $overheadRate,
            'statuses' => Estimate::STATUSES,
            // Cost codes for Work Area dialog
            'costCodes' => \App\Models\CostCode::where('is_active', true)->whereNotNull('qbo_item_id')->orderBy('code')->get(),
        ]);
    }

    public function edit(Estimate $estimate)
    {
        return view('estimates.edit', $this->formData($estimate));
    }

    public function update(Request $request, Estimate $estimate)
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:draft,pending,sent,approved,rejected',
            'client_id' => 'sometimes|required|exists:clients,id',
            'property_id' => 'sometimes|nullable|exists:properties,id',
            'site_visit_id' => 'sometimes|nullable|exists:site_visits,id',
            'expires_at' => 'sometimes|nullable|date',
            'notes' => 'sometimes|nullable|string',
            'terms' => 'sometimes|nullable|string',
            'crew_notes' => 'sometimes|nullable|string',
            'division_id' => 'sometimes|nullable|exists:divisions,id',
            'cost_code_id' => 'sometimes|required|exists:cost_codes,id',
            'estimate_type' => 'sometimes|required|in:design_build,maintenance',
        ]);

        $estimate->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'estimate' => $estimate,
                'message' => 'Estimate updated successfully'
            ]);
        }

        return redirect()->route('estimates.show', $estimate)->with('success', 'Estimate updated');
    }

    public function destroy(Estimate $estimate)
    {
        $estimate->delete();

        return redirect()->route('estimates.index')->with('success', 'Estimate deleted.');
    }

    public function recalculate(Estimate $estimate)
    {
        $this->itemService->recalculateTotals($estimate);
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Totals recalculated',
                'totals' => [
                    'cost_total' => $estimate->cost_total,
                    'revenue_total' => $estimate->revenue_total,
                    'grand_total' => $estimate->grand_total,
                ],
            ]);
        }
        
        return back()->with('success', 'Estimate totals recalculated.');
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
            'estimate_type' => 'required|in:design_build,maintenance',
            'division_id' => 'nullable|exists:divisions,id',
            'cost_code_id' => 'required|exists:cost_codes,id',
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
            'divisions' => \App\Models\Division::orderBy('sort_order')->get(),
            'costCodes' => \App\Models\CostCode::where('is_active', true)->whereNotNull('qbo_item_id')->orderBy('code')->get(),
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

    public function print(Estimate $estimate, Request $request)
    {
        $template = $request->query('template', 'full-detail');
        $download = $request->boolean('download', false);
        
        // Validate template
        $validTemplates = ['full-detail', 'proposal', 'materials-only', 'labor-only', 'summary'];
        if (!in_array($template, $validTemplates)) {
            $template = 'full-detail';
        }
        
        $scopeSummaries = ScopeSummaryBuilder::fromEstimate($estimate);
        
        // Group items by work area
        $itemsByArea = $estimate->items->groupBy('area_id');
        
        // Filter items based on template (proposal shows all items, just hides prices in view)
        $filteredItemsByArea = $itemsByArea->map(function ($items) use ($template) {
            if ($template === 'materials-only') {
                return $items->where('item_type', 'material');
            } elseif ($template === 'labor-only') {
                return $items->where('item_type', 'labor');
            }
            return $items;
        });

        $viewData = [
            'estimate' => $estimate,
            'scopeSummaries' => $scopeSummaries,
            'template' => $template,
            'itemsByArea' => $filteredItemsByArea,
        ];
        
        // Use template-specific view if it exists, otherwise use default
        $viewName = "estimates.print-templates.{$template}";
        if (!view()->exists($viewName)) {
            $viewName = 'estimates.print';
        }
        
        // If download is requested, generate PDF
        if ($download) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewName, $viewData);
            $filename = "Estimate-{$estimate->id}-" . ucfirst(str_replace('-', '_', $template)) . ".pdf";
            return $pdf->download($filename);
        }
        
        return view($viewName, $viewData);
    }

    public function costAnalysisReport(Estimate $estimate, Request $request)
    {
        $download = $request->boolean('download', false);
        
        // Calculate total costs and revenue
        $materialItems = $estimate->items->where('item_type', 'material');
        $laborItems = $estimate->items->where('item_type', 'labor');
        
        $materialsCost = $materialItems->sum(function ($item) {
            return $item->quantity * ($item->unit_cost ?? 0);
        });
        $materialsRevenue = $materialItems->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
        
        $laborCost = $laborItems->sum(function ($item) {
            return $item->quantity * ($item->unit_cost ?? 0);
        });
        $laborRevenue = $laborItems->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
        
        $totalCost = $materialsCost + $laborCost;
        $totalRevenue = $materialsRevenue + $laborRevenue;
        $grossProfit = $totalRevenue - $totalCost;
        $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        
        // Group by work area
        $itemsByArea = $estimate->items->groupBy('area_id')->map(function ($items, $areaId) {
            $area = $items->first()->area ?? null;
            $areaName = $area ? $area->name : 'Unassigned';
            
            $areaCost = $items->sum(function ($item) {
                return $item->quantity * ($item->unit_cost ?? 0);
            });
            $areaRevenue = $items->sum(function ($item) {
                return $item->quantity * $item->unit_price;
            });
            
            return [
                'name' => $areaName,
                'items' => $items,
                'cost' => $areaCost,
                'revenue' => $areaRevenue,
                'profit' => $areaRevenue - $areaCost,
                'margin' => $areaRevenue > 0 ? (($areaRevenue - $areaCost) / $areaRevenue) * 100 : 0,
            ];
        });
        
        $viewData = [
            'estimate' => $estimate,
            'totalCost' => $totalCost,
            'totalRevenue' => $totalRevenue,
            'grossProfit' => $grossProfit,
            'profitMargin' => $profitMargin,
            'materialsCost' => $materialsCost,
            'materialsRevenue' => $materialsRevenue,
            'laborCost' => $laborCost,
            'laborRevenue' => $laborRevenue,
            'itemsByArea' => $itemsByArea,
        ];
        
        if ($download) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('estimates.reports.cost-analysis', $viewData);
            return $pdf->download("Estimate-{$estimate->id}-Cost-Analysis.pdf");
        }
        
        return view('estimates.reports.cost-analysis', $viewData);
    }

    public function laborHoursReport(Estimate $estimate, Request $request)
    {
        $download = $request->boolean('download', false);
        
        $laborItems = $estimate->items->where('item_type', 'labor');
        
        $totalHours = $laborItems->sum('quantity');
        $totalLaborCost = $laborItems->sum(function ($item) {
            return $item->quantity * ($item->unit_cost ?? 0);
        });
        $totalLaborRevenue = $laborItems->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
        
        // Group by work area
        $laborByArea = $laborItems->groupBy('area_id')->map(function ($items, $areaId) {
            $area = $items->first()->area ?? null;
            $areaName = $area ? $area->name : 'Unassigned';
            
            $hours = $items->sum('quantity');
            $cost = $items->sum(function ($item) {
                return $item->quantity * ($item->unit_cost ?? 0);
            });
            $revenue = $items->sum(function ($item) {
                return $item->quantity * $item->unit_price;
            });
            
            return [
                'name' => $areaName,
                'hours' => $hours,
                'cost' => $cost,
                'revenue' => $revenue,
                'items' => $items,
            ];
        })->sortByDesc('hours')->values();
        
        $viewData = [
            'estimate' => $estimate,
            'totalHours' => $totalHours,
            'totalLaborCost' => $totalLaborCost,
            'totalLaborRevenue' => $totalLaborRevenue,
            'laborByArea' => $laborByArea,
        ];
        
        if ($download) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('estimates.reports.labor-hours', $viewData);
            return $pdf->download("Estimate-{$estimate->id}-Labor-Hours.pdf");
        }
        
        return view('estimates.reports.labor-hours', $viewData);
    }

    public function materialRequirementsReport(Estimate $estimate, Request $request)
    {
        $download = $request->boolean('download', false);
        
        // Eager load relationships for better performance
        $estimate->load(['items.material.supplier', 'items.area']);
        
        $materialItems = $estimate->items->where('item_type', 'material');
        
        $totalItems = $materialItems->count();
        $totalCost = $materialItems->sum(function ($item) {
            return $item->quantity * ($item->unit_cost ?? 0);
        });
        $totalRevenue = $materialItems->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
        
        // Consolidate materials by name
        $consolidatedMaterials = $materialItems->groupBy('name')->map(function ($items, $name) {
            $firstItem = $items->first();
            $totalQty = $items->sum('quantity');
            $avgCost = $items->avg('unit_cost') ?? 0;
            $avgPrice = $items->avg('unit_price');
            
            return [
                'name' => $name,
                'description' => $firstItem->description,
                'unit' => $firstItem->unit ?? 'ea',
                'quantity' => $totalQty,
                'unit_cost' => $avgCost,
                'unit_price' => $avgPrice,
                'total_cost' => $totalQty * $avgCost,
                'total_price' => $totalQty * $avgPrice,
            ];
        })->sortBy('name')->values();
        
        // Group by supplier (using material->supplier relationship)
        $materialsBySupplier = $materialItems->groupBy(function ($item) {
            // Access supplier through material relationship
            if ($item->material && $item->material->supplier) {
                return $item->material->supplier->name;
            }
            return 'Unassigned Supplier';
        })->map(function ($items, $supplierName) {
            $firstSupplier = null;
            foreach ($items as $item) {
                if ($item->material && $item->material->supplier) {
                    $firstSupplier = $item->material->supplier;
                    break;
                }
            }
            
            return [
                'items' => $items->map(function ($item) {
                    return [
                        'name' => $item->name,
                        'sku' => $item->material->sku ?? '',
                        'unit' => $item->unit ?? 'ea',
                        'quantity' => $item->quantity,
                        'unit_cost' => $item->unit_cost ?? 0,
                        'total_cost' => $item->quantity * ($item->unit_cost ?? 0),
                    ];
                }),
                'total' => $items->sum(function ($item) {
                    return $item->quantity * ($item->unit_cost ?? 0);
                }),
                'contact' => $firstSupplier ? ($firstSupplier->email ?? $firstSupplier->phone ?? '') : '',
            ];
        })->sortByDesc('total');
        
        // Group by work area
        $materialsByArea = $materialItems->groupBy('area_id')->map(function ($items, $areaId) {
            $area = $items->first()->area ?? null;
            $areaName = $area ? $area->name : 'Unassigned';
            
            $totalCost = $items->sum(function ($item) {
                return $item->quantity * ($item->unit_cost ?? 0);
            });
            $totalPrice = $items->sum(function ($item) {
                return $item->quantity * $item->unit_price;
            });
            
            return [
                'name' => $areaName,
                'items' => $items,
                'total_cost' => $totalCost,
                'total_price' => $totalPrice,
            ];
        })->values();
        
        $viewData = [
            'estimate' => $estimate,
            'totalItems' => $totalItems,
            'totalCost' => $totalCost,
            'totalRevenue' => $totalRevenue,
            'consolidatedMaterials' => $consolidatedMaterials,
            'materialsBySupplier' => $materialsBySupplier,
            'materialsByArea' => $materialsByArea,
        ];
        
        if ($download) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('estimates.reports.material-requirements', $viewData);
            return $pdf->download("Estimate-{$estimate->id}-Material-Requirements.pdf");
        }
        
        return view('estimates.reports.material-requirements', $viewData);
    }

    public function profitMarginReport(Estimate $estimate, Request $request)
    {
        $download = $request->boolean('download', false);
        
        $materialItems = $estimate->items->where('item_type', 'material');
        $laborItems = $estimate->items->where('item_type', 'labor');
        
        $materialsCost = $materialItems->sum(function ($item) {
            return $item->quantity * ($item->unit_cost ?? 0);
        });
        $materialsRevenue = $materialItems->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
        
        $laborCost = $laborItems->sum(function ($item) {
            return $item->quantity * ($item->unit_cost ?? 0);
        });
        $laborRevenue = $laborItems->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
        
        $totalCost = $materialsCost + $laborCost;
        $totalRevenue = $materialsRevenue + $laborRevenue;
        $grossProfit = $totalRevenue - $totalCost;
        $grossProfitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        
        // Estimate net profit (gross profit minus overhead - assume 15% overhead)
        $overhead = $totalRevenue * 0.15;
        $netProfit = $grossProfit - $overhead;
        $netProfitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;
        
        // Profit by work area
        $profitByArea = $estimate->items->groupBy('area_id')->map(function ($items, $areaId) use ($totalRevenue) {
            $area = $items->first()->area ?? null;
            $areaName = $area ? $area->name : 'Unassigned';
            
            $materialItems = $items->where('item_type', 'material');
            $laborItems = $items->where('item_type', 'labor');
            
            $materialsCost = $materialItems->sum(function ($item) {
                return $item->quantity * ($item->unit_cost ?? 0);
            });
            $materialsRevenue = $materialItems->sum(function ($item) {
                return $item->quantity * $item->unit_price;
            });
            
            $laborCost = $laborItems->sum(function ($item) {
                return $item->quantity * ($item->unit_cost ?? 0);
            });
            $laborRevenue = $laborItems->sum(function ($item) {
                return $item->quantity * $item->unit_price;
            });
            
            $areaCost = $materialsCost + $laborCost;
            $areaRevenue = $materialsRevenue + $laborRevenue;
            $areaProfit = $areaRevenue - $areaCost;
            $areaMargin = $areaRevenue > 0 ? ($areaProfit / $areaRevenue) * 100 : 0;
            
            return [
                'name' => $areaName,
                'cost' => $areaCost,
                'revenue' => $areaRevenue,
                'profit' => $areaProfit,
                'margin' => $areaMargin,
                'percent_of_total' => $totalRevenue > 0 ? ($areaRevenue / $totalRevenue) * 100 : 0,
                'materials_cost' => $materialsCost,
                'materials_revenue' => $materialsRevenue,
                'labor_cost' => $laborCost,
                'labor_revenue' => $laborRevenue,
            ];
        })->sortByDesc('margin')->values();
        
        $viewData = [
            'estimate' => $estimate,
            'totalCost' => $totalCost,
            'totalRevenue' => $totalRevenue,
            'grossProfit' => $grossProfit,
            'grossProfitMargin' => $grossProfitMargin,
            'netProfit' => $netProfit,
            'netProfitMargin' => $netProfitMargin,
            'materialsCost' => $materialsCost,
            'materialsRevenue' => $materialsRevenue,
            'laborCost' => $laborCost,
            'laborRevenue' => $laborRevenue,
            'profitByArea' => $profitByArea,
        ];
        
        if ($download) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('estimates.reports.profit-margin', $viewData);
            return $pdf->download("Estimate-{$estimate->id}-Profit-Margin-Analysis.pdf");
        }
        
        return view('estimates.reports.profit-margin', $viewData);
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

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'estimate_ids' => 'required|array',
            'estimate_ids.*' => 'exists:estimates,id',
            'status' => 'required|in:draft,pending,sent,approved,rejected',
        ]);

        $count = \App\Models\Estimate::whereIn('id', $validated['estimate_ids'])
            ->update(['status' => $validated['status']]);

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => "Updated {$count} estimate(s) to " . ucfirst($validated['status']),
        ]);
    }
}
