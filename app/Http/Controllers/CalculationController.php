<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use Illuminate\Http\Request;

class CalculationController extends Controller
{
    public function destroy(Calculation $calculation)
{
    $siteVisitId = $calculation->site_visit_id;
    $calculation->delete();

    return redirect()->back()->with('success', 'Calculation deleted.');
}
}
