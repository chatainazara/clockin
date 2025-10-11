<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Work;
use App\Models\WorkApplication;
use Carbon\Carbon;
use App\Http\Requests\FixRequest;

class AdminAttendanceController extends Controller
{
    public function list(Request $request){
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

    public function show($id){
        // work_applicationsテーブルのwork_idに$idがあってapprove_atに値がなければ以下
        $workApp = WorkApplication::with('work.user','rest_applications')->where('work_id', $id)->whereNull('approve_at')->first();
        if($workApp){
            return view('admin.no_fix',compact('workApp'));
        }else{
        // work_applicationsテーブルのwork_idに$idがない、あってもapprove_atに値があれば以下
            $work = Work::with('rests', 'user')->where('id', $id)->firstOrFail();
            return view('admin.attendance_detail', compact('work'));
        }
    }

    public function fix(FixRequest $request,$id){
        $work = Work::with('rests')->findOrFail($id);
        // 出退勤反映
        $work->update([
            'clock_in_at' => Carbon::parse($request->work_fix['clock_in_at']),
            'clock_out_at' => Carbon::parse($request->work_fix['clock_out_at']),
            'remark' => $request->work_fix['remark'],
        ]);
        $allRests = [];
        // 休憩反映
        foreach ($request->rest_fixes as $restFix) {
            // dd($request->rest_fixes);
            // dd($restFix);
            if ($restFix['rest_id']) { // 既存
                $rest = $work->rests()->find($restFix['rest_id']);
                // if ($rest) {
                $rest->update([
                    'rest_start_at' => Carbon::parse($restFix['rest_start_at']),
                    'rest_end_at' => Carbon::parse($restFix['rest_end_at']),
                ]);
                $allRests[] = [
                    'id' => $restFix['rest_id'],
                    'rest_start_at' => $restFix['rest_start_at'] ? Carbon::parse($restFix['rest_start_at'])->format('H:i') : null,
                    'rest_end_at' => $restFix['rest_end_at'] ? Carbon::parse($restFix['rest_end_at'])->format('H:i') : null,
                ];
                // }
            } elseif($restFix['rest_start_at'] && $restFix['rest_end_at']) { // 新規
                $newRest = $work->rests()->create([
                    'rest_start_at' => $restFix['rest_start_at'],
                    'rest_end_at' => $restFix['rest_end_at'],
                ]);
                // $allRests[] = [
                //     'id' => $newRest->id,
                //     'rest_start_at' => $newRest->rest_start_at ? Carbon::parse($newRest->rest_start_at)->format('H:i') : null,
                //     'rest_end_at' => $newRest->rest_end_at ? Carbon::parse($newRest->rest_end_at)->format('H:i') : null,
                // ];
            }
        }
        $work = Work::with('rests')->findOrFail($id);
        return view('admin.attendance_detail',compact('work'));
    }
}
