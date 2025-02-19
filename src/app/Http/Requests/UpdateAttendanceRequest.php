<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'check_in' => ['required', 'date_format:H:i'],
            'check_out' => [
                'required',
                'date_format:H:i',
                'after:check_in',
                function ($attribute, $value, $fail) {
                    if ($value <= request()->input('check_in')) {
                        $fail('出勤時間もしくは退勤時間が不適切な値です。');
                    }

                    $breakEndTimes = request()->input('break_end', []);
                    foreach ($breakEndTimes as $breakEnd) {
                        if ($value <= $breakEnd) {
                            $fail('出勤時間もしくは退勤時間が不適切な値です。');
                            break;
                        }
                    }

                    $breakStartTimes = request()->input('break_start', []);
                    foreach ($breakStartTimes as $breakStart) {
                        if ($value <= $breakStart) {
                            $fail('出勤時間もしくは退勤時間が不適切な値です。');
                            break;
                        }
                    }
                }
            ],

            'break_start.*' => [
                'nullable',
                'date_format:H:i',
                'after_or_equal:check_in',
                'before_or_equal:check_out',
                function ($attribute, $value, $fail) {
                    if ($value && $value > request()->input('check_out')) {
                        $fail('出勤時間もしくは退勤時間が不適切な値です。');
                    }
                }
            ],
            'break_end.*' => [
                'nullable',
                'date_format:H:i',
                'after:break_start.*',
                'before_or_equal:check_out',
                function ($attribute, $value, $fail) {
                    if ($value && $value > request()->input('check_out')) {
                        $fail('休憩時間が勤務時間外です。');
                        $fail('出勤時間もしくは退勤時間が不適切な値です。');
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
