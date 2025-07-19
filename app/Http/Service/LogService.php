<?php

namespace App\Http\Service;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LogService
{
    public function getWeeklyData()
    {
        $userId = Auth::id();
        $today = CarbonImmutable::now();
        $startOfWeek = $today->startOfWeek(CarbonImmutable::MONDAY)->toDateTimeString();
        $endOfWeek = $today->endOfWeek(CarbonImmutable::SUNDAY)->toDateTimeString();

        $rawResults = DB::table('logs')
            ->selectRaw("DAYNAME(start_date) as day, SUM(TIMESTAMPDIFF(HOUR, start_date, end_date)) as total_hours")
            ->where('user_id', $userId)
            ->whereBetween('start_date', [$startOfWeek, $endOfWeek])
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->groupBy(DB::raw('DAYNAME(start_date)'))
            ->pluck('total_hours', 'day');

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $hoursPerDay = [];
        $weeklyHours = 0;

        foreach ($days as $day) {
            $hours = (int) ($rawResults[$day] ?? 0);
            $hoursPerDay[] = $hours;
            $weeklyHours += $hours;
        }

        return [
            'weekly_day_log' => [
                'labels' => $days,
                'data' => $hoursPerDay,
            ],
            'total_week' => $weeklyHours,
        ];
    }

    public function getMonthlyData()
    {
        $userId = Auth::id();
        $currentYear = now()->year;

        $monthlyTotals = DB::table('logs')
            ->selectRaw('MONTH(start_date) as month, SUM(TIMESTAMPDIFF(SECOND, start_date, end_date)) / 3600 as total_hours')
            ->whereYear('start_date', $currentYear)
            ->where('user_id', $userId)
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->groupBy(DB::raw('MONTH(start_date)'))
            ->pluck('total_hours', 'month');

        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $monthlyData = [];
        foreach (range(1, 12) as $monthNumber) {
            $monthlyData[] = round($monthlyTotals[$monthNumber] ?? 0, 2);
        }

        return [
            'monthly_log' => [
                'labels' => $months,
                'data' => $monthlyData,
            ],
            'total_year' => array_sum($monthlyData),
        ];
    }
}
