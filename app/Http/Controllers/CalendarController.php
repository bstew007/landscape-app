<?php

namespace App\Http\Controllers;

use App\Models\SiteVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        try {
            $currentMonth = Carbon::create($year, $month, 1)->startOfDay();
        } catch (\Exception $exception) {
            $currentMonth = now()->startOfMonth();
            $month = $currentMonth->month;
            $year = $currentMonth->year;
        }

        $startOfCalendar = $currentMonth->copy()->startOfMonth()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar = $currentMonth->copy()->endOfMonth()->endOfWeek(Carbon::SATURDAY);

        $visitsByDate = SiteVisit::with(['client', 'property'])
            ->whereBetween('visit_date', [$startOfCalendar, $endOfCalendar])
            ->orderBy('visit_date')
            ->get()
            ->groupBy(fn (SiteVisit $visit) => $visit->visit_date->format('Y-m-d'));

        $weeks = [];
        $cursor = $startOfCalendar->copy();

        while ($cursor <= $endOfCalendar) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $dateKey = $cursor->format('Y-m-d');
                $week[] = [
                    'date' => $cursor->copy(),
                    'in_month' => $cursor->month === $currentMonth->month,
                    'is_today' => $cursor->isToday(),
                    'visits' => $visitsByDate->get($dateKey, collect()),
                ];

                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        $previousMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();

        return view('calendar.index', [
            'weeks' => $weeks,
            'currentMonth' => $currentMonth,
            'previousMonth' => $previousMonth,
            'nextMonth' => $nextMonth,
        ]);
    }
}
