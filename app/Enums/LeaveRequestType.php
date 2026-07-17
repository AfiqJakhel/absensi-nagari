<?php

namespace App\Enums;

enum LeaveRequestType: string
{
    case PERMISSION = 'permission';
    case SICK = 'sick';
    
    public function label(): string
    {
        return match($this) {
            self::PERMISSION => 'Izin',
            self::SICK => 'Sakit',
        };
    }
}
