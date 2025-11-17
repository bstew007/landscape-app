<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CostCode;
use App\Models\Division;
use Illuminate\Http\Request;

class CostCodeController extends Controller
{
    public function index()
    {
        $codes = CostCode::with('division')->orderBy('code')->get();
        return view('admin.cost-codes.index', compact('codes'));
    }

    public function create()
    {
        return view('admin.cost-codes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:cost_codes,code',
            'name' => 'required|string|max:255',
            'division_id' => 'nullable|exists:divisions,id',
            'is_active' => 'sometimes|boolean',
        ]);
        $data['is_active'] = (bool)($data['is_active'] ?? true);
        CostCode::create($data);
        return redirect()->route('admin.cost-codes.index')->with('success','Cost code created');
    }

    public function edit(CostCode $cost_code)
    {
        return view('admin.cost-codes.edit', ['code' => $cost_code]);
    }

    public function update(Request $request, CostCode $cost_code)
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:cost_codes,code,'.$cost_code->id,
            'name' => 'required|string|max:255',
            'division_id' => 'nullable|exists:divisions,id',
            'qbo_item_id' => 'nullable|string|max:100',
            'qbo_item_name' => 'nullable|string|max:500',
            'is_active' => 'sometimes|boolean',
        ]);
        $cost_code->update($data);
        return redirect()->route('admin.cost-codes.index')->with('success','Cost code updated');
    }

    public function destroy(CostCode $cost_code)
    {
        $cost_code->delete();
        return redirect()->route('admin.cost-codes.index')->with('success','Cost code deleted');
    }
}
