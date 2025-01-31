<?php

namespace App\Http\Controllers;

use App\Http\Service\LogService;
use App\Models\Log;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    protected $logService;

    public function __construct(LogService $logService)
    {
        $this->logService = $logService;
    }

    public function index()
    {
        //Get Weekly Data
        $weekWiseData= $this->logService->getWeeklyData();

        $weeklyData = $weekWiseData['weekly_day_log'];
        $weeklyHours = $weekWiseData['total_week'];

        //Get Monthly Data
        $monthWiseData= $this->logService->getMonthlyData();
        $monthlyData = $monthWiseData['monthly_log'];

        $currentMonth = CarbonImmutable::now()->month;
        $monthNumber = date('n', strtotime($currentMonth));

        $monthlyHours = $monthlyData['data'][$monthNumber-1] ?? 0;

        $lastEntry = Log::latest('id')->first();

        $last_activity = [
            'check_in' => NULL,
            'check_out' => NULL
        ];
        if(!empty($lastEntry)){
            if(!empty($lastEntry->end_date)){
                $endDateInBerlin = Carbon::parse($lastEntry->end_date)
                    ->format('F j, Y, h:i A');

                $last_activity['check_out'] = $endDateInBerlin;
            }
            if(!empty($lastEntry->start_date)){
                $startDateInBerlin = Carbon::parse($lastEntry->start_date)
                    ->format('F j, Y, h:i A');

                $last_activity['check_in'] = $startDateInBerlin;
            }
        }

        return view('dashboard', compact('weeklyData','monthlyData','weeklyHours','monthlyHours','lastEntry','last_activity'));
    }

    public function saveLog(Request $request)
    {
        $request->validate([
            'checkin' => 'nullable|date_format:Y-m-d H:i',
            'checkout' => 'nullable|date_format:Y-m-d H:i',
            'description' => 'nullable|string|max:255',
        ]);

        if ($request->has('checkin')) {

            Log::create([
                'start_date' => Carbon::parse($request->checkin),
                'description' => $request->description,
                'user_id' => Auth::id()
            ]);
        } elseif ($request->has('checkout')) {

            $lastEntry = Log::latest('id')->first();

            if ($lastEntry && $lastEntry->end_date === null) {
                $lastEntry->update([
                    'end_date' => Carbon::parse($request->checkout),
                    'description' => $request->description,
                ]);
            }
        }

        return redirect()->route('dashboard')->with('success', 'Log entry saved successfully!');
    }

}
