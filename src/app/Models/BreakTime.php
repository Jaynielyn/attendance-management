<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakTime extends Model
{
    use HasFactory;

    /**
     * テーブル名
     */
    protected $table = 'breaks';

    /**
     * 一括代入可能な属性
     */
    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
    ];

    /**
     * 勤怠データとのリレーション
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function editBreakTimes()
    {
        return $this->hasMany(EditBreakTime::class, 'break_id');
    }

    /**
     * 休憩時間の計算 (分単位)
     */
    public function getDurationInMinutesAttribute()
    {
        if ($this->break_start && $this->break_end) {
            return $this->break_start->diffInMinutes($this->break_end);
        }

        return 0;
    }

    /**
     * 休憩時間の計算 (時:分形式)
     */
    public function getFormattedDurationAttribute()
    {
        if ($this->break_start && $this->break_end) {
            $diffInMinutes = $this->break_start->diffInMinutes($this->break_end);
            $hours = floor($diffInMinutes / 60);
            $minutes = $diffInMinutes % 60;

            return sprintf('%02d:%02d', $hours, $minutes);
        }

        return '00:00';
    }
}
