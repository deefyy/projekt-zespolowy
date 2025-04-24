<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use Illuminate\Http\Request;
use Carbon\Carbon;
class DashboardController extends Controller
{
    public function index()
    {
        $upcomingCompetitions = Competition::where('start_date', '>=', now())
            ->orderBy('start_date')
            ->take(3)
            ->get();

            $calendarEvents = Competition::all()->map(function ($competition) {
                return [
                    'title' => $competition->name,
                    'start' => Carbon::parse($competition->start_date)->toDateString(),
                    'end'   => Carbon::parse($competition->end_date)->toDateString(),
                    'url'   => route('competitions.show', $competition),
                ];
            });
            
            return view('dashboard', compact('upcomingCompetitions', 'calendarEvents'));
    }
}
