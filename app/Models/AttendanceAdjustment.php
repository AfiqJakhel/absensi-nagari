<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceAdjustment extends Model
{
    protected $fillable = [
        'attendance_id',
        'changed_by',
        'previous_data',
        'updated_data',
        'reason',
    ];
    
    protected $casts = [
        'previous_data' => 'array',
        'updated_data' => 'array',
    ];
    
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
    
    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
