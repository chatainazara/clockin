<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Work;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function list(Request $request)
    {
        // 表示日付（パラメータがなければ今日）
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : Carbon::today();

        // 当日の勤怠データを取得
        $works = Work::with(['user', 'rests'])
            ->whereDate('work_date', $date)
            ->get()
            ->map(function ($work) {
                // 出勤・退勤
                $start = $work->clock_in_at ? Carbon::parse($work->clock_in_at) : null;
                $end = $work->clock_out_at ? Carbon::parse($work->clock_out_at) : null;

                // 休憩時間合計（分）
                $restMinutes = $work->rests->reduce(function ($carry, $rest) {
                    $s = $rest->rest_start_at ? Carbon::parse($rest->rest_start_at) : null;
                    $e = $rest->rest_end_at ? Carbon::parse($rest->rest_end_at) : null;
                    return $carry + ($s && $e ? $e->diffInMinutes($s) : 0);
                }, 0);

                // 勤務時間（分）
                $workMinutes = ($start && $end) ? $end->diffInMinutes($start) - $restMinutes : null;

                // 表示用文字列
                $work->start_display = $start ? $start->format('H:i') : '-';
                $work->end_display = $end ? $end->format('H:i') : '-';
                $work->rest_display = $restMinutes ? sprintf('%d:%02d', floor($restMinutes/60), $restMinutes % 60) : '-';
                $work->work_display = $workMinutes ? sprintf('%d:%02d', floor($workMinutes/60), $workMinutes % 60) : '-';

                return $work;
            });

        return view('admin.attendance_list', compact('works', 'date'));
    }

    public function show($id)
    {
        $work = Work::with(['user', 'rests'])->findOrFail($id);
        return view('admin.attendance_list', compact('work'));
    }
}
