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

        return back()->with('success', 'Line item added.');
    }

    public function update(Request $request, Estimate $estimate, EstimateItem $item)
    {
        $this->authorizeItem($estimate, $item);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit' => ['nullable', 'string', 'max:50'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $updated = $this->service->updateItem($item, $data);

        if ($request->ajax() || $request->wantsJson()) {
            $estimate->refresh();
            return response()->json([
                'item' => $updated,
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
