<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function getTotalBreakTimeAttribute()
    {
        return $this->breakTimes->sum('duration_in_minutes'); // すべての休憩時間の合計
    }

    public function editRequests()
    {
        return $this->hasMany(EditRequest::class);
    }


    protected $casts = [
        'date' => 'datetime',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];
}
