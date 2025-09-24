<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\WorkApplication;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkApplicationController extends Controller
{
    public function index(Request $request)
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

    public function approveView(Request $request,$attendance_correct_request_id){
        $id = $attendance_correct_request_id;
        $workApp = WorkApplication::with('rest_applications','work.user')->where('id',$id)->first();
                // dd($workApp);
        return view('admin.approve',compact('workApp'));
    }



    public function approve(Request $request, $id)
    {
        try {
            $workApp = WorkApplication::with(['work.rests', 'rest_applications'])->findOrFail($id);

            // 既に承認済み
            if ($workApp->approve_at) {
                return response()->json([
                    'status' => 'already_approved',
                    'message' => 'この申請は既に承認済みです。'
                ]);
            }

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

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

}
