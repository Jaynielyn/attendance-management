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
            'check_out' => ['required', 'date_format:H:i', 'after:check_in'],
            'break_times.*.start' => ['nullable', 'date_format:H:i', 'after_or_equal:check_in', 'before_or_equal:check_out'],
            'break_times.*.end' => ['nullable', 'date_format:H:i', 'after:break_times.*.start', 'before_or_equal:check_out'],
            'remarks' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'check_out.after' => '出勤時間もしくは退勤時間が不適切な値です。',
            'break_times.*.start.after_or_equal' => '休憩時間が勤務時間外です。',
            'break_times.*.start.before_or_equal' => '休憩時間が勤務時間外です。',
            'break_times.*.end.after' => '休憩時間が勤務時間外です。',
            'break_times.*.end.before_or_equal' => '休憩時間が勤務時間外です。',
            'break_times.*.start.date_format' => '休憩時間が勤務時間外です。',
            'break_times.*.end.date_format' => '休憩時間が勤務時間外です。',
            'remarks.required' => '備考を記入してください。',
        ];
    }

}