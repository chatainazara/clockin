<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkApplication;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Requests\ApplicationRequest;
use App\Models\Work;
use App\Models\RestApplication;

class WorkApplicationController extends Controller
{
    public function application(ApplicationRequest $request)
    {
        // work_id から Work を取得
        $work = Work::findOrFail($request->input('work_id'));
        DB::transaction(function () use ($request, $work) {
            $clockIn  = $request->input('work_application.clock_in_at');
            $clockOut = $request->input('work_application.clock_out_at');
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
                $restStart = $restApp['rest_start_at']
                    ? Carbon::parse($workDate . ' ' . $restApp['rest_start_at'])
                    : null;
                $restEnd = $restApp['rest_end_at']
                    ? Carbon::parse($workDate . ' ' . $restApp['rest_end_at'])
                    : null;
                    if($restStart && $restEnd){
                        RestApplication::create([
                        'work_application_id' => $workApp->id,
                        'rest_id'             => $restApp['rest_id'] ?: null,
                        'rest_start_at'       => $restStart,
                        'rest_end_at'         => $restEnd,
                    ]);
                }
            }
        });
        return redirect('/attendance');
    }

    public function adminIndex(Request $request)
    {
        $status = $request->query('status', 'pending');
        $query = WorkApplication::with(['work.user'])
            ->orderBy('created_at', 'desc');
        if ($status === 'pending') {
            $query->whereNull('approve_at');
        } elseif ($status === 'approved') {
            $query->whereNotNull('approve_at');
        }
        $applications = $query->get();
        return view('admin.application_list', compact('applications', 'status'));
    }

    public function userIndex(Request $request)
    {
        $status = $request->query('status', 'pending');
        $query = WorkApplication::with(['work.user'])
                ->whereHas('work', function ($q) {
                    $q->where('user_id', auth()->id()); // 現在ログイン中のユーザー
                })
            ->orderBy('created_at', 'desc');
        if ($status === 'pending') {
            $query->whereNull('approve_at');
        } elseif ($status === 'approved') {
            $query->whereNotNull('approve_at');
        }
        $applications = $query->get();
        return view('auth.application_list', compact('applications', 'status'));
    }

    public function approveView(Request $request,$attendance_correct_request_id){
        $id = $attendance_correct_request_id;
        $workApp = WorkApplication::with('rest_applications','work.user')->where('id',$id)->first();
        return view('admin.approve',compact('workApp'));
    }

    public function approve(Request $request, $id)
    {
            $workApp = WorkApplication::with(['work.rests', 'rest_applications'])->findOrFail($id);
            $work = $workApp->work;
            // 出退勤反映
            $work->update([
                'clock_in_at' => $workApp->clock_in_at,
                'clock_out_at' => $workApp->clock_out_at,
            ]);
            $allRests = [];
            // 休憩反映
            foreach ($workApp->rest_applications as $restApp) {
                if ($restApp->rest_id) { // 既存
                    $rest = $work->rests()->find($restApp->rest_id);
                    if ($rest) {
                        $rest->update([
                            'rest_start_at' => $restApp->rest_start_at,
                            'rest_end_at' => $restApp->rest_end_at,
                        ]);
                        $allRests[] = [
                            'id' => $rest->id,
                            'rest_start_at' => $rest->rest_start_at ? Carbon::parse($rest->rest_start_at)->format('H:i') : null,
                            'rest_end_at' => $rest->rest_end_at ? Carbon::parse($rest->rest_end_at)->format('H:i') : null,
                        ];
                    }
                } else { // 新規
                    $newRest = $work->rests()->create([
                        'rest_start_at' => $restApp->rest_start_at,
                        'rest_end_at' => $restApp->rest_end_at,
                    ]);
                    $allRests[] = [
                        'id' => $newRest->id,
                        'rest_start_at' => $newRest->rest_start_at ? Carbon::parse($newRest->rest_start_at)->format('H:i') : null,
                        'rest_end_at' => $newRest->rest_end_at ? Carbon::parse($newRest->rest_end_at)->format('H:i') : null,
                    ];
                }
            }
            // 承認日時セット
            $workApp->update(['approve_at' => now()]);
            return response()->json([
                'status' => 'ok',
                'clock_in_at' => $work->clock_in_at ? Carbon::parse($work->clock_in_at)->format('H:i') : null,
                'clock_out_at' => $work->clock_out_at ? Carbon::parse($work->clock_out_at)->format('H:i') : null,
                'rests' => $allRests,
            ]);
    }
}
