<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class ApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_application.clock_in_at'      => ['required', 'date_format:H:i'],
            'work_application.clock_out_at'     => ['required', 'date_format:H:i'],
            'work_application.reason'           => ['required'],
            'rest_applications.*.rest_start_at' => ['nullable', 'date_format:H:i'],
            'rest_applications.*.rest_end_at'   => ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages(): array
    {
        return [
            'work_application.clock_in_at.required'  => '出勤時間を入力してください',
            'work_application.clock_out_at.required' => '退勤時間を入力してください',
            'work_application.clock_in_at.date_format' => '出勤時間は時刻形式(HH:MM)で入力してください',
            'work_application.clock_out_at.date_format' => '退勤時間は時刻形式(HH:MM)で入力してください',
            'work_application.reason.required' => '備考を記入してください',
            'rest_applications.*.rest_start_at.date_format' => '休憩開始時間は時刻形式(HH:MM)で入力してください',
            'rest_applications.*.rest_end_at.date_format'   => '休憩終了時間は時刻形式(HH:MM)で入力してください',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $clockIn  = $this->input('work_application.clock_in_at');
            $clockOut = $this->input('work_application.clock_out_at');
            if ($clockIn && $clockOut) {
                $in  = Carbon::createFromFormat('H:i', $clockIn);
                $out = Carbon::createFromFormat('H:i', $clockOut);
                // 出勤・退勤の前後関係
                if ($in->gte($out)) {
                    $validator->errors()->add(
                        'work_application.clock_in_at',
                        '出勤時間もしくは退勤時間が不適切な値です'
                    );
                }
                $rests = [];
                foreach ($this->input('rest_applications', []) as $index => $rest) {
                    $restStart = !empty($rest['rest_start_at'])
                        ? Carbon::createFromFormat('H:i', $rest['rest_start_at'])
                        : null;
                    $restEnd = !empty($rest['rest_end_at'])
                        ? Carbon::createFromFormat('H:i', $rest['rest_end_at'])
                        : null;
                    // 出退勤と比較
                    if ($restStart && ($restStart->lt($in) || $restStart->gt($out))) {
                        $validator->errors()->add(
                            "rest_applications.$index.rest_start_at",
                            '休憩時間が不適切な値です'
                        );
                    }
                    if ($restEnd && $restEnd->gt($out)) {
                        $validator->errors()->add(
                            "rest_applications.$index.rest_end_at",
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }
                    if ($restStart && $restEnd && $restEnd->lt($restStart)) {
                        $validator->errors()->add(
                            "rest_applications.$index.rest_end_at",
                            '休憩時間の終了が開始より前になっています'
                        );
                    }
                    // 重複判定用に配列へ格納
                    if ($restStart && $restEnd) {
                        $rests[] = [
                            'index' => $index,
                            'start' => $restStart,
                            'end'   => $restEnd,
                        ];
                    }
                }
                // 休憩同士の重複チェック
                for ($i = 0; $i < count($rests); $i++) {
                    for ($j = $i + 1; $j < count($rests); $j++) {
                        $a = $rests[$i];
                        $b = $rests[$j];
                        // AとBが重なっているか判定
                        if ($a['start']->lt($b['end']) && $b['start']->lt($a['end'])) {
                            $validator->errors()->add(
                                "rest_applications.{$a['index']}.rest_start_at",
                                '休憩時間が他の休憩時間とかぶっています'
                            );
                            $validator->errors()->add(
                                "rest_applications.{$b['index']}.rest_start_at",
                                '休憩時間が他の休憩時間とかぶっています'
                            );
                        }
                    }
                }
            }

        });
    }
}
