<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('pl');

        $base     = $request->query('month');           
        $firstDay = $base
            ? Carbon::createFromFormat('Y-m', $base)->startOfMonth()
            : Carbon::today()->startOfMonth();

        $daysInMonth = $firstDay->daysInMonth;
        $offset      = $firstDay->dayOfWeekIso - 1;     

        $upcomingCompetitions = Competition::whereDate('end_date', '>=', Carbon::today())
            ->orderBy('start_date')
            ->take(5)
            ->get();

        $eventsByDay = Competition::whereMonth('start_date', $firstDay->month)
            ->whereYear ('start_date', $firstDay->year)
            ->get()
            ->groupBy(fn ($c) => Carbon::parse($c->start_date)->day);  

        $prevUrl = route('home', ['month' => $firstDay->copy()->subMonth()->format('Y-m')]) . '#calendar';
        $nextUrl = route('home', ['month' => $firstDay->copy()->addMonth()->format('Y-m')]) . '#calendar';

        return view('dashboard', [
            'upcomingCompetitions' => $upcomingCompetitions,
            'daysInMonth'  => $daysInMonth,
            'offset'       => $offset,
            'eventsByDay'  => $eventsByDay,
            'monthName'    => $firstDay->isoFormat('MMMM YYYY'), 
            'year'         => $firstDay->year,
            'month'        => $firstDay->month,
            'prevUrl'      => $prevUrl,
            'nextUrl'      => $nextUrl,
        ]);
    }
}
