<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'check_in' => ['required', 'date_format:H:i'],
            'check_out' => [
                'required',
                'date_format:H:i',
                'after:check_in',
                function ($attribute, $value, $fail) {
                    $checkIn = request()->input('check_in');
                    if ($value <= $checkIn) {
                        $fail('出勤時間もしくは退勤時間が不適切な値です。');
                    }

                    foreach (request()->input('break_times', []) as $break) {
                        if (!empty($break['end']) && $break['end'] > $value) {
                            $fail('休憩時間が勤務時間外です。');
                            $fail('出勤時間もしくは退勤時間が不適切な値です。');
                        }
                        if (!empty($break['start']) && $break['start'] > $value) {
                            $fail('休憩時間が勤務時間外です。');
                            $fail('出勤時間もしくは退勤時間が不適切な値です。');
                        }
                    }
                }
            ],

            'break_times.*.start' => [
                'nullable',
                'date_format:H:i',
                'after_or_equal:check_in',
                'before_or_equal:check_out',
                function ($attribute, $value, $fail) {
                    $checkIn = request()->input('check_in');
                    $checkOut = request()->input('check_out');
                    if ($value && ($value < $checkIn || $value > $checkOut)) {
                        $fail('休憩時間が勤務時間外です。');
                        $fail('出勤時間もしくは退勤時間が不適切な値です。');
                    }
                }
            ],
            'break_times.*.end' => [
                'nullable',
                'date_format:H:i',
                'after:break_times.*.start',
                'before_or_equal:check_out',
                function ($attribute, $value, $fail) {
                    $checkOut = request()->input('check_out');

                    if ($value && $value > $checkOut) {
                        $fail('休憩時間が勤務時間外です。');
                        $fail('出勤時間もしくは退勤時間が不適切な値です。');
                    }

                    foreach (request()->input('break_times', []) as $break) {
                        if (!empty($break['start']) && $value <= $break['start']) {
                            $fail('休憩終了時間が休憩開始時間より後である必要があります。');
                        }
                    }
                }
            ],
            'remarks' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'check_out.after' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_start.*.after_or_equal' => '休憩時間が勤務時間外です。',
            'break_start.*.before_or_equal' => '休憩時間が勤務時間外です。',
            'break_end.*.after' => '休憩時間が勤務時間外です。',
            'break_end.*.before_or_equal' => '休憩時間が勤務時間外です。',
            'remarks.required' => '備考を記入してください。',
        ];
    }
}