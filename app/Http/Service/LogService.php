<?php

namespace App\Http\Service;

use App\Models\Log;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;

class LogService
{
    public function getWeeklyData()
    {
        $today = CarbonImmutable::now();
        $startOfWeek = $today->startOfWeek(CarbonImmutable::MONDAY);
        $endOfWeek = $today->endOfWeek(CarbonImmutable::SUNDAY);

        $logs = Log::whereBetween('start_date', [$startOfWeek, $endOfWeek])
            ->where('user_id', Auth::id())
            ->get();

        $weeklyHours = 0;

        $hoursPerDay = [
            'Monday' => 0,
            'Tuesday' => 0,
            'Wednesday' => 0,
            'Thursday' => 0,
            'Friday' => 0,
            'Saturday' => 0,
            'Sunday' => 0,
        ];

        if ($logs->isEmpty()) {
            return ['weekly_day_log' => $hoursPerDay, 'total_week' => $weeklyHours];
        }

        if (!empty($logs)) {
            foreach ($logs as $log) {
                $startDate = CarbonImmutable::parse($log->start_date);
                $endDate = CarbonImmutable::parse($log->end_date);

                if (!empty($log->start_date) && !empty($log->end_date)) {
                    $hoursWorked = $endDate->diffInHours($startDate);
                    $dayOfWeek = $startDate->format('l');

                    $hoursPerDay[$dayOfWeek] += $hoursWorked;

                    $weeklyHours = $weeklyHours + $hoursWorked;
                }
            }

            $weeklyData = [
                'labels' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'data' => [
                    $hoursPerDay['Monday'],
                    $hoursPerDay['Tuesday'],
                    $hoursPerDay['Wednesday'],
                    $hoursPerDay['Thursday'],
                    $hoursPerDay['Friday'],
                    $hoursPerDay['Saturday'],
                    $hoursPerDay['Sunday'],
                ],
            ];
        }

        return ['weekly_day_log' => $weeklyData, 'total_week' => $weeklyHours];
    }

    public function getMonthlyData()
    {

        $currentYear = CarbonImmutable::now()->year;

        $startOfYear = CarbonImmutable::createFromDate($currentYear, 1, 1)->startOfDay();
        $endOfYear = CarbonImmutable::createFromDate($currentYear, 12, 31)->endOfDay();

        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        $monthlyData = array_fill_keys($months, 0);

        $logs = Log::WhereBetween('start_date', [$startOfYear, $endOfYear])
            ->where('user_id', Auth::id())
            ->get();

        if ($logs->isEmpty()) {
            $monthlyChartData = [
                'labels' => $months,
                'data' => array_values($monthlyData),
            ];
            return ['monthly_log' => $monthlyChartData, 'total_month' => 0];
        }

        foreach ($logs as $log) {
            $startDate = CarbonImmutable::parse($log->start_date);
            $endDate = CarbonImmutable::parse($log->end_date);

            if (!empty($log->start_date) && !empty($log->end_date)) {
                if ($startDate->year < $currentYear) {
                    $startDate = $startOfYear;
                }
                if ($endDate->year > $currentYear) {
                    $endDate = $endOfYear;
                }

                $currentDate = $startDate;
                while ($currentDate->lte($endDate)) {
                    $monthName = $currentDate->format('F');

                    if ($currentDate->isSameDay($endDate)) {
                        $hoursWorked = $endDate->diffInHours($currentDate);
                    } else {
                        $hoursWorked = $currentDate->endOfDay()->diffInHours($currentDate);
                    }

                    $monthlyData[$monthName] += $hoursWorked;
                    $currentDate = $currentDate->addDay();
                }

                $monthlyChartData = [
                    'labels' => $months,
                    'data' => array_values($monthlyData),
                ];
            }
        }

        return ['monthly_log' => $monthlyChartData];
    }
}
