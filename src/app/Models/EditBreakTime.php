<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditBreakTime extends Model
{
    use HasFactory;

    protected $fillable = ['edit_request_id', 'new_break_start', 'new_break_end','break_id',];

    public function editRequest()
    {
        return $this->belongsTo(EditRequest::class);
    }

    public function break()
    {
        return $this->belongsTo(BreakTime::class, 'break_id');
    }

}
