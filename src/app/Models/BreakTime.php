<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BreakTime extends Model
{
    use HasFactory;

    protected $table = 'breaks';

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
            $start = Carbon::parse($this->break_start);
            $end = Carbon::parse($this->break_end);
            return $start->diffInMinutes($end);
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
