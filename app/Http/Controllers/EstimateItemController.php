<?php

namespace App\Http\Controllers;

use App\Models\Estimate;
use App\Models\EstimateItem;
use App\Services\EstimateItemService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EstimateItemController extends Controller
{
    public function __construct(protected EstimateItemService $service)
    {
    }

    public function store(Request $request, Estimate $estimate)
    {
        $data = $this->validateItem($request);

        $payload = $this->buildPayloadFromRequest($data);
        $payload['item_type'] = $data['item_type'];
        $payload['quantity'] = (float) ($data['quantity'] ?? 0);
        $payload['unit_cost'] = (float) ($payload['unit_cost'] ?? 0);
        $payload['unit_price'] = array_key_exists('unit_price', $data)
            ? (float) $data['unit_price']
            : ($payload['unit_price'] ?? null);
        $payload['margin_rate'] = array_key_exists('margin_rate', $data)
            ? (float) $data['margin_rate']
            : ($payload['margin_rate'] ?? null);
        $payload['tax_rate'] = (float) ($payload['tax_rate'] ?? 0);
        $payload['source'] = $payload['source'] ?? 'manual';

        if (empty($payload['name'])) {
            $payload['name'] = ucfirst($payload['item_type']) . ' Item';
        }

        $item = $this->service->createManualItem($estimate, $payload);

        if ($request->ajax() || $request->wantsJson()) {
            $estimate->refresh();
            return response()->json([
                'item' => $item,
                'totals' => $this->summarizeTotals($estimate),
            ]);
        }

        $response = back()
            ->with('success', 'Line item added.')
            ->with('recent_item_id', $item->id);

        if ($request->boolean('stay_in_add_items')) {
            $response->with('reopen_add_items', true)
                ->with('add_items_tab', $request->input('add_items_tab', $payload['item_type'] ?? 'materials'));
        }

        return $response;
    }

    public function update(Request $request, Estimate $estimate, EstimateItem $item)
    {
        $this->authorizeItem($estimate, $item);

        // Special case: allow area-only updates for quick assignment
        $onlyArea = $request->has('area_id')
            && ! $request->has('name')
            && ! $request->has('quantity')
            && ! $request->has('unit_cost')
            && ! $request->has('unit_price')
            && ! $request->has('margin_rate')
            && ! $request->has('tax_rate')
            && ! $request->has('unit')
            && ! $request->has('description');
        if ($onlyArea) {
            $data = $request->validate([
                'area_id' => ['nullable', 'integer', 'exists:estimate_areas,id'],
            ]);
            $updated = $this->service->updateItem($item, $data);
            if ($request->ajax() || $request->wantsJson()) {
                $estimate->refresh();
                return response()->json([
                    'item' => $updated,
                    'totals' => $this->summarizeTotals($estimate),
                ]);
            }
            return back()->with('success', 'Item area updated.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit' => ['nullable', 'string', 'max:50'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'margin_rate' => ['nullable', 'numeric', 'min:-0.99', 'max:10'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
            'area_id' => ['nullable', 'integer', 'exists:estimate_areas,id'],
        ]);

        $updated = $this->service->updateItem($item, $data);

        if ($request->ajax() || $request->wantsJson()) {
            $estimate->refresh();
            return response()->json([
                'item' => $updated,
                'totals' => $this->summarizeTotals($estimate),
            ]);
        }

        return back()->with('success', 'Line item updated.');
    }

    public function destroy(Estimate $estimate, EstimateItem $item)
    {
        $this->authorizeItem($estimate, $item);
        $this->service->deleteItem($item);

        return back()->with('success', 'Line item removed.');
    }

    public function reorder(Request $request, Estimate $estimate)
    {
        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer'],
        ]);

        $ids = $validated['order'];
        $items = $estimate->items()->whereIn('id', $ids)->get(['id']);
        $validIds = $items->pluck('id')->all();

        // Ensure all IDs belong to this estimate
        $ordered = array_values(array_filter($ids, fn($id) => in_array($id, $validIds, true)));

        foreach ($ordered as $index => $id) {
            EstimateItem::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        $estimate->refresh();

        return response()->json([
            'status' => 'ok',
            'totals' => $this->summarizeTotals($estimate),
        ]);
    }

    protected function summarizeTotals(Estimate $estimate): array
    {
        $estimate->loadMissing('items');

        $grouped = $estimate->items->groupBy('item_type');

        return [
            'material_subtotal' => $estimate->material_subtotal,
            'material_cost_total' => $estimate->material_cost_total,
            'material_profit_total' => $grouped->get('material', collect())->sum('margin_total'),
            'labor_subtotal' => $estimate->labor_subtotal,
            'labor_cost_total' => $estimate->labor_cost_total,
            'labor_profit_total' => $grouped->get('labor', collect())->sum('margin_total'),
            'fee_total' => $estimate->fee_total,
            'fee_cost_total' => $grouped->get('fee', collect())->sum('cost_total'),
            'fee_profit_total' => $grouped->get('fee', collect())->sum('margin_total'),
            'discount_total' => $estimate->discount_total,
            'discount_cost_total' => $grouped->get('discount', collect())->sum('cost_total'),
            'discount_profit_total' => $grouped->get('discount', collect())->sum('margin_total'),
            'tax_total' => $estimate->tax_total,
            'grand_total' => $estimate->grand_total,
            'revenue_total' => $estimate->revenue_total,
            'cost_total' => $estimate->cost_total,
            'profit_total' => $estimate->profit_total,
            'net_profit_total' => $estimate->net_profit_total,
            'profit_margin' => $estimate->profit_margin,
            'net_margin' => $estimate->net_margin,
        ];
    }

    protected function validateItem(Request $request): array

    {
        $hasCatalog = (bool) ($request->input('catalog_type') && $request->input('catalog_id'));
        return $request->validate([
            'item_type' => ['required', Rule::in(['material', 'labor', 'fee', 'discount'])],
            'catalog_type' => ['nullable', Rule::in(['material', 'labor'])],
            'catalog_id' => ['nullable', 'integer'],
            'name' => [Rule::requiredIf(!$hasCatalog), 'nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit' => ['nullable', 'string', 'max:50'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit_cost' => [Rule::requiredIf(!$hasCatalog), 'nullable', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'margin_rate' => ['nullable', 'numeric', 'min:-0.99', 'max:10'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
        ], [
            'name.required' => 'Please enter a name for the item or choose one from the catalog.',
            'unit_cost.required' => 'Please enter a unit cost or choose from the catalog to auto-fill pricing.',
        ]);
    }

    protected function buildPayloadFromRequest(array $data): array
    {
        $catalogType = $data['catalog_type'] ?? null;
        $catalogId = $data['catalog_id'] ?? null;

        $payload = [
            'catalog_type' => null,
            'catalog_id' => null,
            'name' => $data['name'] ?? 'Line Item',
            'unit' => $data['unit'] ?? null,
            'unit_cost' => $data['unit_cost'] ?? 0,
            'unit_price' => $data['unit_price'] ?? null,
            'margin_rate' => $data['margin_rate'] ?? null,
            'tax_rate' => $data['tax_rate'] ?? 0,
            'description' => $data['description'] ?? null,
        ];

        if ($catalogType && $catalogId) {
            $defaults = $this->service->resolveCatalogDefaults($catalogType, $catalogId);
            $payload = array_merge($payload, $defaults, [
                'catalog_type' => $defaults['catalog_type'] ?? null,
                'catalog_id' => $catalogId,
            ]);
        }

        // If no margin/price provided, fall back to the default margin (via catalog defaults or budget)
        if (!array_key_exists('unit_price', $payload) && isset($payload['unit_cost'])) {
            $payload['unit_price'] = round(((float) $payload['unit_cost']) * (1 + (float) ($payload['margin_rate'] ?? 0)), 2);
        }

        if (array_key_exists('margin_rate', $data)) {
            $payload['margin_rate'] = $data['margin_rate'];
        }
        if (array_key_exists('unit_price', $data)) {
            $payload['unit_price'] = $data['unit_price'];
        }

        if (empty($payload['name'])) {
            $payload['name'] = 'Line Item';
        }

        return $payload;
    }

    protected function authorizeItem(Estimate $estimate, EstimateItem $item): void
    {
        if ($item->estimate_id !== $estimate->id) {
            abort(404);
        }
    }
}
