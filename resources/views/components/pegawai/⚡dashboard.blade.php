<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Enums\AttendanceStatus;
use Illuminate\Support\Carbon;

new class extends Component
{
    use WithPagination;

    public $monthFilter = '';

    protected $queryString = [
        'monthFilter' => ['except' => ''],
    ];

    public function mount()
    {
        if (empty($this->monthFilter)) {
            $this->monthFilter = now()->format('Y-m');
        }
    }

    public function getGreeting()
    {
        $hour = now()->hour;
        if ($hour < 11) return 'Selamat pagi';
        if ($hour < 15) return 'Selamat siang';
        if ($hour < 19) return 'Selamat sore';
        return 'Selamat malam';
    }

    public function with(): array
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        // 1. Get today's active schedule for the user
        $schedule = $user->schedules()
            ->whereDate('attendance_date', $today)
            ->where('is_active', true)
            ->first();

        // 2. Get today's attendance record
        $todayAttendance = $user->attendances()
            ->whereDate('attendance_date', $today)
            ->first();

        // Calculate Total Working Hours and Lateness
        $workingHours = '-';
        $lateness = '-';

        if ($todayAttendance) {
            if ($todayAttendance->check_in_at && $todayAttendance->check_out_at) {
                $checkIn = Carbon::parse($todayAttendance->check_in_at);
                $checkOut = Carbon::parse($todayAttendance->check_out_at);
                $hours = $checkIn->diffInHours($checkOut);
                $minutes = $checkIn->diffInMinutes($checkOut) % 60;
                $workingHours = "{$hours} Jam {$minutes} Menit";
            } elseif ($todayAttendance->check_in_at) {
                $workingHours = 'Sedang Bekerja';
            }

            if ($todayAttendance->late_minutes > 0) {
                $lateness = "{$todayAttendance->late_minutes} Menit";
            }
        }

        // Get list of last 6 months for filtering options
        $monthsList = [];
        for ($i = 0; $i < 6; $i++) {
            $date = now()->subMonths($i);
            $monthsList[$date->format('Y-m')] = $date->translatedFormat('F Y');
        }

        // 3. Query monthly history
        $historyData = $user->attendances()
            ->whereYear('attendance_date', Carbon::parse($this->monthFilter)->year)
            ->whereMonth('attendance_date', Carbon::parse($this->monthFilter)->month)
            ->get()
            ->keyBy(fn($item) => $item->attendance_date->toDateString());

        $monthDate = Carbon::parse($this->monthFilter);
        $startOfMonth = $monthDate->copy()->startOfMonth();
        $daysInMonth = $monthDate->daysInMonth;
        // 0 = Sunday, 1 = Monday. We want Monday to be first column in grid.
        $startDayOfWeek = $startOfMonth->dayOfWeekIso; // 1 (Mon) to 7 (Sun)
        
        return [
            'greeting' => $this->getGreeting(),
            'schedule' => $schedule,
            'workingHours' => $workingHours,
            'lateness' => $lateness,
            'monthsList' => $monthsList,
            'todayAttendance' => $todayAttendance,
            'historyData' => $historyData,
            'startDayOfWeek' => $startDayOfWeek,
            'daysInMonth' => $daysInMonth,
        ];
    }
};
?>

<div class="space-y-6">
    <!-- Hero Section / Greeting Card -->
    <div class="relative bg-white rounded-[1.5rem] overflow-hidden shadow-sm p-6 md:p-8 border border-gray-100 min-h-[160px] flex items-center">
        <!-- Gradient background matching the design -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#eef2ff] via-white/95 to-transparent z-10"></div>
        
        <!-- Background Image (Rumah Gadang) on the right side -->
        <div class="absolute inset-y-0 right-0 w-full md:w-1/2 bg-cover bg-left z-0 opacity-20 md:opacity-100" style="background-image: url('{{ asset('images/bg-login.png') }}'); mask-image: linear-gradient(to right, transparent, black 40%); -webkit-mask-image: linear-gradient(to right, transparent, black 40%);"></div>

        <div class="relative z-20 w-full flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
            <div>
                <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-2">
                    {{ $greeting }}, {{ auth()->user()->name }}!
                </h2>
                <p class="text-gray-600 text-sm md:text-base font-medium">Semangat bekerja hari ini!</p>
            </div>
            
            <div class="flex items-center gap-4 text-xs md:text-sm text-gray-700 font-semibold bg-white/80 backdrop-blur-md px-4 py-2.5 rounded-xl border border-gray-100 shadow-sm">
                <div class="flex items-center gap-2">
                    <i class="fa-regular fa-calendar text-emerald-600 text-base"></i>
                    <span>{{ \Illuminate\Support\Carbon::today()->translatedFormat('l, d F Y') }}</span>
                </div>
                <div class="h-4 w-px bg-gray-300"></div>
                <div class="flex items-center gap-2">
                    <i class="fa-regular fa-clock text-emerald-600 text-base"></i>
                    <span id="realtime-clock">{{ \Illuminate\Support\Carbon::now()->translatedFormat('H:i') }} WIB</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Information Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        <!-- Jam Masuk (Check-in) -->
        @php
            $masukBorderClass = 'border border-gray-100';
            $masukIconBg = 'bg-emerald-50';
            $masukIconText = 'text-emerald-600';
            $masukIconBorder = 'border-emerald-100';
            
            if ($todayAttendance && $todayAttendance->check_in_at) {
                if ($todayAttendance->status->value === 'present') {
                    $masukBorderClass = 'border-2 border-blue-500';
                    $masukIconBg = 'bg-blue-50';
                    $masukIconText = 'text-blue-600';
                    $masukIconBorder = 'border-blue-100';
                } elseif ($todayAttendance->status->value === 'late') {
                    $masukBorderClass = 'border-2 border-red-500';
                    $masukIconBg = 'bg-red-50';
                    $masukIconText = 'text-red-600';
                    $masukIconBorder = 'border-red-100';
                }
            }
        @endphp
        <div class="bg-white p-5 rounded-2xl {{ $masukBorderClass }} shadow-sm flex items-center gap-4 transition-all hover:shadow-md duration-200">
            <div class="w-12 h-12 rounded-xl {{ $masukIconBg }} border {{ $masukIconBorder }} flex items-center justify-center {{ $masukIconText }}">
                <i class="fa-solid fa-right-to-bracket text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-0.5">Jam Masuk (Check-in)</p>
                <p class="text-base font-bold text-gray-800">
                    {{ $todayAttendance && $todayAttendance->check_in_at ? \Illuminate\Support\Carbon::parse($todayAttendance->check_in_at)->format('H:i') . ' WIB' : '-' }}
                </p>
            </div>
        </div>

        <!-- Jam Pulang (Check-out) -->
        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center gap-4 transition-all hover:shadow-md duration-200">
            <div class="w-12 h-12 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-500">
                <i class="fa-solid fa-right-from-bracket text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-0.5">Jam Pulang (Check-out)</p>
                <p class="text-base font-bold text-gray-800">
                    {{ $todayAttendance && $todayAttendance->check_out_at ? \Illuminate\Support\Carbon::parse($todayAttendance->check_out_at)->format('H:i') . ' WIB' : '-' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Attendance History Section -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <!-- Section Header / Filters -->
        <div class="p-5 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-gray-500 text-lg"></i>
                <h3 class="text-base font-bold text-gray-900">Riwayat Absensi</h3>
            </div>
            
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <div class="relative w-full sm:w-48">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-regular fa-calendar text-sm"></i>
                    </div>
                    <select wire:model.live="monthFilter" class="pl-10 pr-8 py-2.5 bg-white border border-gray-200 hover:border-gray-300 rounded-xl text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 shadow-sm transition-all duration-200 outline-none text-gray-700 w-full appearance-none font-semibold cursor-pointer">
                        @foreach ($monthsList as $val => $label)
                            <option value="{{ $val }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </div>
                </div>

            </div>
        </div>

        <!-- Calendar Area -->
        <div class="p-6">
            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4">Kalender Kehadiran</h4>
            <div class="grid grid-cols-7 gap-2 text-center mb-2">
                <div class="text-xs font-bold text-gray-400">Sen</div>
                <div class="text-xs font-bold text-gray-400">Sel</div>
                <div class="text-xs font-bold text-gray-400">Rab</div>
                <div class="text-xs font-bold text-gray-400">Kam</div>
                <div class="text-xs font-bold text-gray-400">Jum</div>
                <div class="text-xs font-bold text-gray-400">Sab</div>
                <div class="text-xs font-bold text-gray-400 text-red-400">Min</div>
            </div>
            
            <div class="grid grid-cols-7 gap-2">
                {{-- Empty cells before start of month --}}
                @for ($i = 1; $i < $startDayOfWeek; $i++)
                    <div class="aspect-square rounded-xl bg-gray-50/50 border border-gray-50"></div>
                @endfor

                {{-- Days of month --}}
                @for ($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $dateStr = \Illuminate\Support\Carbon::parse($monthFilter)->setDay($day)->toDateString();
                        $record = $historyData[$dateStr] ?? null;
                        
                        $bgClass = 'bg-gray-50 border-gray-100 hover:border-gray-200';
                        $textClass = 'text-gray-400';
                        $statusText = '';
                        
                        if ($record) {
                            $textClass = 'text-gray-900';
                            switch($record->status->value) {
                                case 'present':
                                    $bgClass = 'bg-emerald-50 border-emerald-200 shadow-sm shadow-emerald-100/50';
                                    $statusText = 'Hadir';
                                    $textClass = 'text-emerald-700';
                                    break;
                                case 'late':
                                    $bgClass = 'bg-amber-50 border-amber-200 shadow-sm shadow-amber-100/50';
                                    $statusText = 'Telat';
                                    $textClass = 'text-amber-700';
                                    break;
                                case 'permission':
                                    $bgClass = 'bg-blue-50 border-blue-200 shadow-sm shadow-blue-100/50';
                                    $statusText = 'Izin';
                                    $textClass = 'text-blue-700';
                                    break;
                                case 'sick':
                                    $bgClass = 'bg-purple-50 border-purple-200 shadow-sm shadow-purple-100/50';
                                    $statusText = 'Sakit';
                                    $textClass = 'text-purple-700';
                                    break;
                                case 'absent':
                                    $bgClass = 'bg-red-50 border-red-200 shadow-sm shadow-red-100/50';
                                    $statusText = 'Alfa';
                                    $textClass = 'text-red-700';
                                    break;
                            }
                        }
                    @endphp
                    <div class="aspect-square rounded-xl border {{ $bgClass }} p-1 md:p-1.5 flex flex-col justify-between transition-colors relative group">
                        <span class="text-xs md:text-sm font-bold {{ $textClass }} ml-1">{{ $day }}</span>
                        @if($record)
                            <span class="text-[8px] md:text-[9px] font-extrabold uppercase {{ $textClass }} text-center leading-tight">
                                {{ $statusText }}
                            </span>
                            
                            {{-- Tooltip for time --}}
                            @if($record->check_in_at)
                            <div class="absolute inset-x-0 bottom-full mb-2 hidden group-hover:block z-10">
                                <div class="bg-gray-900 text-white text-[10px] rounded-lg py-1 px-2 whitespace-nowrap text-center shadow-lg">
                                    {{ \Illuminate\Support\Carbon::parse($record->check_in_at)->format('H:i') }}
                                    @if($record->check_out_at)
                                        - {{ \Illuminate\Support\Carbon::parse($record->check_out_at)->format('H:i') }}
                                    @endif
                                    <div class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-900"></div>
                                </div>
                            </div>
                            @endif
                        @endif
                    </div>
                @endfor
            </div>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', () => {
            const clockEl = document.getElementById('realtime-clock');
            if (clockEl) {
                setInterval(() => {
                    const now = new Date();
                    // Mengambil waktu GMT+7 (Asia/Jakarta)
                    const timeString = now.toLocaleTimeString('id-ID', {
                        timeZone: 'Asia/Jakarta',
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    });
                    clockEl.textContent = `${timeString.replace('.', ':')} WIB`;
                }, 1000);
            }
        });
    </script>
</div>
