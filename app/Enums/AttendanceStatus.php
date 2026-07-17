<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case PRESENT = 'present';
    case LATE = 'late';
    case PERMISSION = 'permission';
    case SICK = 'sick';
    case ABSENT = 'absent';
    
    public function label(): string
    {
        return match($this) {
            self::PRESENT => 'Hadir',
            self::LATE => 'Terlambat',
            self::PERMISSION => 'Izin',
            self::SICK => 'Sakit',
            self::ABSENT => 'Alfa',
        };
    }
}
