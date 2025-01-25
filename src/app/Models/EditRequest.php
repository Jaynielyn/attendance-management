<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditRequest extends Model
{
    use HasFactory;

    protected $table = 'edit_requests';

    protected $fillable = [
        'user_id',
        'attendance_id',
        'reason',
        'new_date',
        'new_check_in',
        'new_check_out',
        'new_break_start',
        'new_break_end',
        'approval_status',
        'requested_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
