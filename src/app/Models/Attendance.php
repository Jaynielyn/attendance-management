<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    // テーブル名を指定（省略可能、Laravelが自動的に推測しますが、明示的に指定します）
    protected $table = 'attendances';

    protected $fillable = [
        'user_id',
        'date',
        'check_in',
        'check_out',
        'break_start',
        'break_end',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $dates = [
        'check_in',
        'check_out',
        'break_start',
        'break_end',
        'date',
    ];
}
