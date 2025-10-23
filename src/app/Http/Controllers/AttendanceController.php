<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Work;
use App\Models\Rest;
use Carbon\Carbon;
use App\Models\WorkApplication;
use App\Models\RestApplication;
use Illuminate\Support\Facades\DB;


class AttendanceController extends Controller
{
    public function register() {
    $today = Carbon::today()->toDateString();
    $todayWork = Work::with('rests')
        ->where('user_id', Auth::id())
        ->whereDate('work_date', $today)
        ->first();
    $leaveWork = false;
    if ($todayWork && $todayWork->clock_out_at) {
        $leaveWork = true;
    }
    return view('auth.attendance', compact('todayWork','leaveWork'));
    }

    public function action(Request $request)
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();
        $todayWork = Work::with('rests')
            ->where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();
        $action = $request->input('action');
        switch ($action) {
            case 'clockIn':
                if (!$todayWork) {
                    Work::create([
                        'user_id' => $user->id,
                        'work_date' => $today,
                        'clock_in_at' => now(),
                    ]);
                }
                break;
            case 'clockOut':
                if ($todayWork && !$todayWork->clock_out_at) {
                    $todayWork->update([
                        'clock_out_at' => now(),
                    ]);
                }
                break;
            case 'restStart':
                if ($todayWork) {
                    $rest = Rest::create([
                        'work_id' => $todayWork->id,
                        'rest_start_at' => now(),
                    ]);
                }
                break;
            case 'restEnd':
                if ($todayWork) {
                    $resting = $todayWork->rests()
                        ->whereNotNull('rest_start_at')
                        ->whereNull('rest_end_at')
                        ->first();
                    if ($resting) {
                        $resting->update([
                            'rest_end_at' => now(),
                        ]);
                    }
                }
                break;
        }
        return redirect('attendance');
    }

    public function list(Request $request){
        $user = Auth::user();
        // 表示する年月をリクエストから受け取る。なければ最新の月
        $yearMonth = $request->input('month', Carbon::now()->format('Y-m'));
        $start = Carbon::parse($yearMonth . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();
        // 指定月の全勤怠データを取得
        $works = Work::with('rests')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();
        // 日ごとの集計を作成
        $days = [];
        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $work = $works->firstWhere('work_date', $date->toDateString());
            $weekday = ['日','月','火','水','木','金','土'][$date->dayOfWeek];
            if ($work) {
                $clockIn = $work->clock_in_at ? Carbon::parse($work->clock_in_at) : null;
                $clockOut = $work->clock_out_at ? Carbon::parse($work->clock_out_at) : null;
                // 休憩合計
                $restTotal = $work->rests->reduce(function ($carry, $rest) {
                    if ($rest->rest_start_at && $rest->rest_end_at) {
                        $carry += Carbon::parse($rest->rest_end_at)
                                    ->diffInMinutes(Carbon::parse($rest->rest_start_at));
                    }
                    return $carry;
                }, 0);
                // 勤務合計 = 退勤 - 出勤 - 休憩
                $workTotal = null;
                if ($clockIn && $clockOut) {
                    $workMinutes = $clockOut->diffInMinutes($clockIn) - $restTotal;
                    $workTotal = sprintf('%d:%02d', floor($workMinutes / 60), $workMinutes % 60);
                }
                $days[] = [
                    'work_id' => $work->id,
                    'date' => $date->format('m/d'),
                    'weekday' => $weekday,
                    'clock_in' => $clockIn ? $clockIn->format('H:i') : '',
                    'clock_out' => $clockOut ? $clockOut->format('H:i') : '',
                    'rest' => $restTotal > 0 ? sprintf('%d:%02d', floor($restTotal / 60), $restTotal % 60) : '',
                    'total' => $workTotal,
                ];
            } else {
                // データがない日
                $days[] = [
                    'work_id' => '',
                    'date' => $date->format('m/d'),
                    'weekday' => $weekday,
                    'clock_in' => '',
                    'clock_out' => '',
                    'rest' => '',
                    'total' => '',
                ];
            }
        }
        return view('auth.attendance_list', [
            'days' => $days,
            'yearMonth' => $start,
        ]);
    }

    public function detailShow($id)
    {
        // work_idがあってapprove_atに値がなければ以下
        $workApp = WorkApplication::with('work.user','rest_applications')->where('work_id', $id)->whereNull('approve_at')->first();
        if($workApp){
            return view('auth.application',compact('workApp'));
        }else{
        // work_applicationsテーブルのwork_idがない、あってもapprove_atに値があれば以下
            $work = Work::with('rests', 'user')->where('id', $id)->firstOrFail();
            return view('auth.attendance_detail', compact('work'));
        }
    }
}
