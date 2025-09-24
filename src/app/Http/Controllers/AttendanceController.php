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
use App\Http\Requests\ApplicationRequest;


class AttendanceController extends Controller
{
    public function register() {
    $today = Carbon::today()->toDateString();
    $todayWork = Work::with('rests')
        ->where('user_id', Auth::id())
        ->whereDate('work_date', $today)
        ->first();

    return view('auth.attendance', compact('todayWork'));
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

                    if (!$rest) {
                        // デバッグ用: create が失敗した場合
                        dd('Rest create failed');
                    }
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
    //                 $user = auth()->user();
    // dd($user, $role);
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
                    'weekday' => $weekday,  // ← 休みの日でも必要！
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
        $work = Work::with('rests', 'user')->where('id', $id)->firstOrFail();
        return view('auth.attendance_detail', compact('work'));
    }

    public function application(ApplicationRequest $request)
    {
        // work_id から Work を取得
        $work = Work::findOrFail($request->input('work_id'));

        DB::transaction(function () use ($request, $work) {
            $clockIn  = $request->input('work_application.clock_in_at');  // "09:21"
            $clockOut = $request->input('work_application.clock_out_at'); // "18:30"

            // 勤務日 (DATE型) に基づいてDATETIMEを生成
            $workDate = $work->work_date;

            $clockInAt  = $clockIn  ? Carbon::parse($workDate . ' ' . $clockIn)  : null;
            $clockOutAt = $clockOut ? Carbon::parse($workDate . ' ' . $clockOut) : null;

            // 1. 出退勤修正申請を作成
            $workApp = WorkApplication::create([
                'work_id'      => $work->id,
                'clock_in_at'  => $clockInAt,
                'clock_out_at' => $clockOutAt,
                'reason'       => $request->input('work_application.reason'),
            ]);

            // 2. 休憩修正申請を作成
            foreach ($request->input('rest_applications', []) as $restApp) {
                // dd($restApp);
                // dd($request->all());
                $restStart = $restApp['rest_start_at']
                    ? Carbon::parse($workDate . ' ' . $restApp['rest_start_at'])
                    : null;

                $restEnd = $restApp['rest_end_at']
                    ? Carbon::parse($workDate . ' ' . $restApp['rest_end_at'])
                    : null;

                RestApplication::create([
                    'work_application_id' => $workApp->id,
                    'rest_id'             => $restApp['rest_id'] ?: null,
                    'rest_start_at'       => $restStart,
                    'rest_end_at'         => $restEnd,
                ]);
            }
        });

        return redirect('/attendance')
            ->with('status', '修正申請を送信しました');
    }
}
