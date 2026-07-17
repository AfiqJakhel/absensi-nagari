<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name',
        'attendance_date',
        'start_time',
        'end_time',
        'check_in_start',
        'check_in_end',
        'check_out_start',
        'check_out_end',
        'late_tolerance_minutes',
        'location_name',
        'latitude',
        'longitude',
        'radius_meters',
        'location_validation_enabled',
        'is_active',
        'notes',
        'created_by',
    ];
    
    protected $casts = [
        'attendance_date' => 'date',
        'location_validation_enabled' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'schedule_user');
    }
    
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
