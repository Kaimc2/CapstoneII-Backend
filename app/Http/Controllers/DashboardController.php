<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Design;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function get_stats()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $dailyDesigns = Design::whereDate('created_at', $today)->count();
        $dailyCommissions = Commission::whereDate('created_at', $today)->count();

        $weeklyDesigns = Design::whereBetween('created_at', [$weekStart, $today])->count();
        $weeklyCommissions = Commission::whereBetween('created_at', [$weekStart, $today])->count();

        $monthlyDesigns = Design::whereBetween('created_at', [$monthStart, $today])->count();
        $monthlyCommissions = Commission::whereBetween('created_at', [$monthStart, $today])->count();

        return response()->json([
            'daily' => ['designs' => $dailyDesigns, 'commissions' => $dailyCommissions],
            'weekly' => ['designs' => $weeklyDesigns, 'commissions' => $weeklyCommissions],
            'monthly' => ['designs' => $monthlyDesigns, 'commissions' => $monthlyCommissions],
        ]);
    }
}
