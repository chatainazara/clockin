<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Work;

class StaffController extends Controller
{
    public function list(){
        $users = User::all();
        return view('admin.staff_list',compact('users'));
    }

    public function detail(Request $request ,$id){
        $user = User::find($id);
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
        return view('admin.staff_detail', [
            'user' => $user,
            'days' => $days,
            'yearMonth' => $start,
        ]);
    }

    public function csv(Request $request, $id){
        $user = User::find($id);
        $yearMonth = $request->input('month');
        $start = Carbon::parse($yearMonth . '-01')->startOfMonth();
        $end   = $start->copy()->endOfMonth();
        $works = Work::with('rests')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();
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
        // CSV出力処理
        $filename = sprintf('%s_%s勤怠.csv', $user->name, $start->format('Y年m月'));
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        // 出力内容
        $callback = function () use ($days) {
            $file = fopen('php://output', 'w');
            // 日本語が文字化けしないように
            fputs($file, chr(0xEF).chr(0xBB).chr(0xBF));
            // 見出し行
            fputcsv($file, array_keys($days[0]));
            // データ行
            foreach ($days as $line) {
                fputcsv($file, $line);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
}
