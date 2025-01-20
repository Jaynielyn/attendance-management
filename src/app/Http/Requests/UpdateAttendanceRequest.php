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
        return true; // 認可ロジックを必要に応じて追加
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'check_in' => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'remarks' => 'required|string|max:255',
            'year' => ['required', 'regex:/^\d{4}年$/'],
            'month_day' => ['required', 'regex:/^\d{1,2}月\d{1,2}日$/'],
            'break_start.*' => 'nullable|date_format:H:i',
            'break_end.*' => 'nullable|date_format:H:i',
        ];
    }

    public function messages()
    {
        return [
            'check_out.after' => '出勤時間もしくは退勤時間が不適切な値です。',
            'remarks.required' => '備考を記入してください。',
            'year.regex' => '年の形式が正しくありません。（例: 2025年）',
            'month_day.regex' => '月日の形式が正しくありません。（例: 1月20日）',
        ];
    }
}
