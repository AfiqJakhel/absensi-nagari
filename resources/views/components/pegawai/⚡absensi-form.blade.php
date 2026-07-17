<?php

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Enums\AttendanceStatus;
use Illuminate\Support\Carbon;

new class extends Component
{
    public $latitude = null;
    public $longitude = null;
    public $accuracy = null;
    public $notes = '';
    public $locationError = '';
    public $distance = null;

    protected $listeners = ['coordinatesUpdated' => 'updateCoordinates'];

    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth radius in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // returns distance in meters
    }

    public function checkIn()
    {
        try {
            $user = auth()->user();
            $today = Carbon::today()->toDateString();

            // 1. Get schedule
            $schedule = $user->schedules()
                ->whereDate('attendance_date', $today)
                ->where('is_active', true)
                ->first();

            if (!$schedule) {
                session()->flash('error', 'Tidak ada jadwal aktif untuk Anda hari ini.');
                return;
            }

            // 2. Validate timing
            $now = Carbon::now();
            $checkInStart = Carbon::parse($today . ' ' . $schedule->check_in_start);
            $checkInEnd = Carbon::parse($today . ' ' . $schedule->check_in_end);

            if ($now->lt($checkInStart)) {
                session()->flash('error', 'Check-in belum dibuka. Jam masuk dimulai pada ' . $checkInStart->format('H:i') . ' WIB.');
                return;
            }

            if ($now->gt($checkInEnd)) {
                session()->flash('error', 'Check-in sudah ditutup untuk hari ini (Batas waktu: ' . $checkInEnd->format('H:i') . ' WIB).');
                return;
            }

            // 3. Validate already checked in
            $existing = Attendance::where('user_id', $user->id)
                ->where('schedule_id', $schedule->id)
                ->whereDate('attendance_date', $schedule->attendance_date)
                ->first();

            if ($existing && $existing->check_in_at) {
                session()->flash('error', 'Anda sudah melakukan check-in hari ini.');
                return;
            }

            // 4. Validate location if enabled
            if ($schedule->location_validation_enabled) {
                if (is_null($this->latitude) || is_null($this->longitude)) {
                    session()->flash('error', 'Lokasi tidak terdeteksi. Silakan izinkan akses GPS di browser Anda.');
                    return;
                }

                $this->distance = $this->calculateDistance(
                    $this->latitude,
                    $this->longitude,
                    $schedule->latitude,
                    $schedule->longitude
                );

                if ($this->distance > $schedule->radius_meters) {
                    session()->flash('error', 'Absensi ditolak. Anda berada di luar radius kantor (Jarak Anda: ' . round($this->distance) . ' meter, Radius maksimal: ' . $schedule->radius_meters . ' meter).');
                    return;
                }
            }

            // 5. Calculate lateness
            $limitTime = Carbon::parse($today . ' ' . $schedule->start_time)->addMinutes($schedule->late_tolerance_minutes);
            $status = $now->gt($limitTime) ? AttendanceStatus::LATE : AttendanceStatus::PRESENT;
            $lateMinutes = $now->gt($limitTime) ? $now->diffInMinutes($limitTime) : 0;

            // 6. Save attendance
            Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'schedule_id' => $schedule->id,
                    'attendance_date' => $schedule->attendance_date,
                ],
                [
                    'check_in_at' => $now,
                    'check_in_latitude' => $this->latitude,
                    'check_in_longitude' => $this->longitude,
                    'check_in_accuracy' => $this->accuracy,
                    'check_in_ip' => request()->ip(),
                    'check_in_user_agent' => request()->userAgent(),
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'notes' => $this->notes,
                ]
            );

            $this->notes = '';
            session()->flash('success', 'Absen masuk berhasil direkam! Status: ' . ($status === AttendanceStatus::LATE ? 'Terlambat' : 'Hadir'));

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function checkOut()
    {
        try {
            $user = auth()->user();
            $today = Carbon::today()->toDateString();

            // 1. Get schedule
            $schedule = $user->schedules()
                ->whereDate('attendance_date', $today)
                ->where('is_active', true)
                ->first();

            if (!$schedule) {
                session()->flash('error', 'Tidak ada jadwal aktif untuk Anda hari ini.');
                return;
            }

            // 2. Get existing attendance
            $attendance = Attendance::where('user_id', $user->id)
                ->where('schedule_id', $schedule->id)
                ->whereDate('attendance_date', $schedule->attendance_date)
                ->first();

            if (!$attendance || !$attendance->check_in_at) {
                session()->flash('error', 'Anda harus melakukan check-in terlebih dahulu sebelum check-out.');
                return;
            }

            if ($attendance->check_out_at) {
                session()->flash('error', 'Anda sudah melakukan check-out hari ini.');
                return;
            }

            // 3. Validate timing
            $now = Carbon::now();
            $checkOutStart = Carbon::parse($today . ' ' . $schedule->check_out_start);
            $checkOutEnd = Carbon::parse($today . ' ' . $schedule->check_out_end);

            if ($now->lt($checkOutStart)) {
                session()->flash('error', 'Check-out belum dibuka. Jam pulang dimulai pada ' . $checkOutStart->format('H:i') . ' WIB.');
                return;
            }

            if ($now->gt($checkOutEnd)) {
                session()->flash('error', 'Waktu check-out sudah ditutup untuk hari ini (Batas waktu: ' . $checkOutEnd->format('H:i') . ' WIB).');
                return;
            }

            // 4. Validate location if enabled
            if ($schedule->location_validation_enabled) {
                if (is_null($this->latitude) || is_null($this->longitude)) {
                    session()->flash('error', 'Lokasi tidak terdeteksi. Silakan izinkan akses GPS di browser Anda.');
                    return;
                }

                $this->distance = $this->calculateDistance(
                    $this->latitude,
                    $this->longitude,
                    $schedule->latitude,
                    $schedule->longitude
                );

                if ($this->distance > $schedule->radius_meters) {
                    session()->flash('error', 'Absensi ditolak. Anda berada di luar radius kantor (Jarak Anda: ' . round($this->distance) . ' meter, Radius maksimal: ' . $schedule->radius_meters . ' meter).');
                    return;
                }
            }

            // 5. Update attendance for check-out
            $attendance->update([
                'check_out_at' => $now,
                'check_out_latitude' => $this->latitude,
                'check_out_longitude' => $this->longitude,
                'check_out_accuracy' => $this->accuracy,
                'check_out_ip' => request()->ip(),
                'check_out_user_agent' => request()->userAgent(),
            ]);

            session()->flash('success', 'Absen pulang berhasil direkam! Selamat beristirahat.');

        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function with(): array
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();

        $schedule = $user->schedules()
            ->whereDate('attendance_date', $today)
            ->where('is_active', true)
            ->first();

        $attendance = null;
        $isWithinRadius = false;
        $distanceComputed = null;

        if ($schedule) {
            $attendance = Attendance::where('user_id', $user->id)
                ->where('schedule_id', $schedule->id)
                ->whereDate('attendance_date', $schedule->attendance_date)
                ->first();

            // Calculate distance reactively on view rendering if coords exist
            if ($schedule->location_validation_enabled && !is_null($this->latitude) && !is_null($this->longitude)) {
                $distanceComputed = $this->calculateDistance(
                    $this->latitude,
                    $this->longitude,
                    $schedule->latitude,
                    $schedule->longitude
                );
                $isWithinRadius = $distanceComputed <= $schedule->radius_meters;
                $this->distance = $distanceComputed;
            }
        }

        return [
            'schedule' => $schedule,
            'attendance' => $attendance,
            'isWithinRadius' => $isWithinRadius,
            'distanceComputed' => $distanceComputed,
        ];
    }
};
?>

<div class="max-w-2xl mx-auto space-y-6">
    <!-- Clock Widget Card -->
    <div class="relative bg-gradient-to-br from-teal-700 via-teal-800 to-emerald-800 text-white rounded-[1.5rem] shadow-xl p-8 overflow-hidden text-center">
        <!-- background pattern -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_20%,rgba(255,255,255,0.1),transparent)] pointer-events-none"></div>
        <div class="absolute bottom-0 right-0 left-0 h-24 bg-cover opacity-10 pointer-events-none" style="background-image: url('{{ asset('images/bg-login.png') }}'); mask-image: linear-gradient(to top, black, transparent); -webkit-mask-image: linear-gradient(to top, black, transparent);"></div>

        <div class="relative z-10 space-y-2">
            <h2 class="text-sm font-bold tracking-widest text-emerald-300 uppercase">Waktu Server Harian</h2>
            <!-- Big Clock -->
            <div class="text-4xl md:text-5xl font-black tracking-tight" id="realtime-clock">
                {{ \Illuminate\Support\Carbon::now()->format('H:i:s') }}
            </div>
            <p class="text-sm text-teal-100 font-medium" id="realtime-date">
                {{ \Illuminate\Support\Carbon::today()->translatedFormat('l, d F Y') }}
            </p>
        </div>
    </div>

    <!-- Location Status Banner -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-4" x-data="{ showHelp: false }" x-init="setTimeout(() => showHelp = true, 3000)">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center bg-teal-50 text-teal-600 border border-teal-100">
                <i class="fa-solid fa-location-dot text-lg"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-900">Validasi Geolocation</h3>
                <p class="text-xs text-gray-500 font-semibold">Memastikan koordinat absensi Anda presisi.</p>
            </div>
        </div>

        <!-- Location Status Body -->
        <div class="border-t border-gray-50 pt-4 text-sm font-medium">
            @if ($locationError)
                <div class="p-4 bg-red-50 border border-red-100 rounded-xl text-red-700 flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-lg mt-0.5"></i>
                    <div>
                        <p class="font-bold">Akses GPS Gagal</p>
                        <p class="text-xs text-red-600/90 mt-0.5">{{ $locationError }}</p>
                    </div>
                </div>
            @elseif (is_null($latitude) || is_null($longitude))
                <div class="p-4 bg-amber-50 border border-amber-100 rounded-xl text-amber-800 flex flex-col gap-2">
                    <div class="flex items-center gap-3 animate-pulse">
                        <i class="fa-solid fa-circle-notch fa-spin text-lg"></i>
                        <div>
                            <p class="font-bold">Mencari Koordinat...</p>
                            <p class="text-xs text-amber-700 mt-0.5">Silakan berikan izin akses lokasi jika muncul dialog browser.</p>
                        </div>
                    </div>
                    <div x-show="showHelp" class="mt-2 text-xs border-t border-amber-200/50 pt-2 text-amber-950 font-normal space-y-1.5" x-cloak>
                        <p class="font-bold"><i class="fa-solid fa-circle-info mr-1"></i> Jika pencarian lokasi memakan waktu lama:</p>
                        <ul class="list-disc pl-4 space-y-1 text-amber-900/95">
                            <li>Klik ikon <strong>gembok / info bulat (i)</strong> di sebelah kiri alamat URL browser Anda (dekat <code>http://127.0.0.1:8000</code>), lalu pastikan izin <strong>Lokasi (Location)</strong> diatur ke <strong>Izinkan (Allow)</strong>.</li>
                            <li>Pastikan <strong>Layanan Lokasi (Location Services)</strong> di pengaturan Windows/perangkat HP Anda sudah dalam posisi <strong>Aktif / ON</strong>.</li>
                            <li>Koneksi internet Anda saat ini mungkin sedang lambat dalam mendeteksi koordinat IP/Wifi.</li>
                        </ul>
                    </div>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-3.5 bg-gray-50/50 border border-gray-100 rounded-xl text-gray-700 space-y-1">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Koordinat Anda</p>
                        <p class="text-xs font-mono font-bold text-gray-800">
                            Lat: {{ round($latitude, 6) }}, Lon: {{ round($longitude, 6) }}
                        </p>
                        <p class="text-[10px] text-gray-400">Akurasi: ±{{ round($accuracy, 1) }}m</p>
                    </div>

                    @if ($schedule && $schedule->location_validation_enabled)
                        <div class="p-3.5 border rounded-xl flex flex-col justify-center {{ $isWithinRadius ? 'bg-green-50/50 border-green-100 text-green-800' : 'bg-red-50/50 border-red-100 text-red-800' }}">
                            <div class="flex items-center gap-2 mb-1">
                                @if ($isWithinRadius)
                                    <i class="fa-solid fa-circle-check text-green-600 text-base"></i>
                                    <span class="font-bold">Di Dalam Radius</span>
                                @else
                                    <i class="fa-solid fa-circle-xmark text-red-600 text-base"></i>
                                    <span class="font-bold">Di Luar Radius</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 font-medium">
                                Jarak: <span class="font-bold text-gray-900">{{ round($distanceComputed) }} meter</span> dari kantor (Maks: {{ $schedule->radius_meters }}m)
                            </p>
                        </div>
                    @else
                        <div class="p-3.5 bg-blue-50/50 border border-blue-100 text-blue-800 rounded-xl flex items-center gap-2">
                            <i class="fa-solid fa-info-circle text-lg"></i>
                            <div>
                                <p class="font-bold text-xs">Jadwal Tanpa Validasi Geolocation</p>
                                <p class="text-[10px] text-gray-500 mt-0.5">Anda dapat absen dari lokasi mana saja.</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Action Forms Card -->
    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-6">
        <!-- Validation / Status Alerts -->
        @if (session()->has('success'))
            <div class="p-4 bg-green-50 border border-green-100 rounded-xl text-green-800 flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-lg"></i>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="p-4 bg-red-50 border border-red-100 rounded-xl text-red-800 flex items-start gap-3">
                <i class="fa-solid fa-circle-xmark text-lg mt-0.5"></i>
                <p class="text-sm font-semibold">{{ session('error') }}</p>
            </div>
        @endif

        @if (!$schedule)
            <!-- No Schedule -->
            <div class="text-center py-10 space-y-3">
                <div class="w-16 h-16 bg-gray-50 text-gray-400 border rounded-full flex items-center justify-center mx-auto">
                    <i class="fa-regular fa-calendar-xmark text-2xl"></i>
                </div>
                <h4 class="text-base font-bold text-gray-800">Tidak Ada Jadwal Hari Ini</h4>
                <p class="text-xs text-gray-400 max-w-sm mx-auto">Anda tidak dijadwalkan untuk melakukan absensi hari ini. Hubungi administrator jika Anda merasa ini adalah kesalahan.</p>
            </div>
        @else
            <!-- Attendance Steps -->
            <div class="space-y-6">
                <!-- Check-in State -->
                @if (!$attendance || !$attendance->check_in_at)
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-gray-800">1. Ambil Absen Masuk (Check-in)</h4>
                            <p class="text-xs text-gray-400 font-medium">Jendela absensi dibuka mulai: {{ \Carbon\Carbon::parse($schedule->check_in_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->check_in_end)->format('H:i') }} WIB.</p>
                        </div>

                        <!-- Notes Area -->
                        <div class="space-y-1.5">
                            <label for="notes" class="text-xs text-gray-500 font-bold uppercase tracking-wider ml-1">Catatan Keterangan (Opsional)</label>
                            <textarea wire:model="notes" id="notes" rows="2" 
                                class="w-full border border-gray-200 rounded-xl p-3 text-sm focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 outline-none text-gray-700 resize-none shadow-inner" 
                                placeholder="Tulis catatan jika telat, berhalangan, atau lainnya..."></textarea>
                        </div>

                        <!-- Check-in Button -->
                        @php
                            $now = Carbon::now();
                            $checkInStart = Carbon::parse($schedule->attendance_date->toDateString() . ' ' . $schedule->check_in_start);
                            $checkInEnd = Carbon::parse($schedule->attendance_date->toDateString() . ' ' . $schedule->check_in_end);
                            $timeValid = $now->between($checkInStart, $checkInEnd);
                            $locationValid = !$schedule->location_validation_enabled || ($latitude && $longitude && $isWithinRadius);
                            $canCheckIn = $timeValid && $locationValid;
                        @endphp
                        
                        <button wire:click="checkIn" @disabled(!$canCheckIn)
                            class="w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-200 disabled:text-gray-400 disabled:shadow-none transition-all duration-200 transform hover:-translate-y-0.5 disabled:pointer-events-none gap-2">
                            <i class="fa-solid fa-right-to-bracket"></i> Ambil Absen Masuk
                        </button>
                        
                        @if (!$timeValid)
                            <p class="text-center text-xs text-red-500 font-semibold mt-2">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Waktu absensi di luar batas check-in dibuka.
                            </p>
                        @elseif ($schedule->location_validation_enabled && (!$latitude || !$longitude))
                            <p class="text-center text-xs text-amber-600 font-semibold mt-2">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Menunggu deteksi koordinat GPS aktif...
                            </p>
                        @elseif ($schedule->location_validation_enabled && !$isWithinRadius)
                            <p class="text-center text-xs text-red-500 font-semibold mt-2">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Lokasi Anda berada di luar radius kantor yang diizinkan.
                            </p>
                        @endif
                    </div>

                <!-- Check-out State -->
                @elseif (!$attendance->check_out_at)
                    <div class="space-y-4">
                        <div class="p-4 bg-emerald-50/50 border border-emerald-100 rounded-xl text-emerald-900 space-y-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-800">Status Check-in Anda</p>
                            <p class="text-sm font-bold text-gray-800">
                                Berhasil check-in pada pukul: <span class="font-extrabold text-emerald-700">{{ \Carbon\Carbon::parse($attendance->check_in_at)->format('H:i') }} WIB</span>
                            </p>
                            <p class="text-[11px] text-gray-500 font-semibold">
                                Status kehadiran: <span class="font-bold">{{ $attendance->status->label() }}</span>
                            </p>
                        </div>

                        <div class="space-y-1 pt-2">
                            <h4 class="text-sm font-bold text-gray-800">2. Ambil Absen Pulang (Check-out)</h4>
                            <p class="text-xs text-gray-400 font-medium">Jendela check-out dibuka mulai: {{ \Carbon\Carbon::parse($schedule->check_out_start)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->check_out_end)->format('H:i') }} WIB.</p>
                        </div>

                        <!-- Check-out Button -->
                        @php
                            $now = Carbon::now();
                            $checkOutStart = Carbon::parse($schedule->attendance_date->toDateString() . ' ' . $schedule->check_out_start);
                            $checkOutEnd = Carbon::parse($schedule->attendance_date->toDateString() . ' ' . $schedule->check_out_end);
                            $timeValidOut = $now->between($checkOutStart, $checkOutEnd);
                            $locationValidOut = !$schedule->location_validation_enabled || ($latitude && $longitude && $isWithinRadius);
                            $canCheckOut = $timeValidOut && $locationValidOut;
                        @endphp
                        
                        <button wire:click="checkOut" @disabled(!$canCheckOut)
                            class="w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 disabled:bg-gray-200 disabled:text-gray-400 disabled:shadow-none transition-all duration-200 transform hover:-translate-y-0.5 disabled:pointer-events-none gap-2">
                            <i class="fa-solid fa-right-from-bracket"></i> Ambil Absen Pulang
                        </button>
                        
                        @if (!$timeValidOut)
                            <p class="text-center text-xs text-red-500 font-semibold mt-2">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Waktu check-out belum dibuka / sudah ditutup. Jam pulang dimulai pukul {{ \Carbon\Carbon::parse($schedule->check_out_start)->format('H:i') }} WIB.
                            </p>
                        @elseif ($schedule->location_validation_enabled && (!$latitude || !$longitude))
                            <p class="text-center text-xs text-amber-600 font-semibold mt-2">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Menunggu deteksi koordinat GPS aktif...
                            </p>
                        @elseif ($schedule->location_validation_enabled && !$isWithinRadius)
                            <p class="text-center text-xs text-red-500 font-semibold mt-2">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Lokasi Anda berada di luar radius kantor yang diizinkan.
                            </p>
                        @endif
                    </div>

                <!-- Done State -->
                @else
                    <div class="text-center py-10 space-y-4">
                        <div class="w-16 h-16 bg-emerald-50 text-emerald-600 border border-emerald-100 rounded-full flex items-center justify-center mx-auto shadow-md">
                            <i class="fa-solid fa-circle-check text-3xl"></i>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-base font-extrabold text-gray-800">Absensi Hari Ini Selesai</h4>
                            <p class="text-xs text-gray-400 max-w-sm mx-auto font-medium">Terima kasih atas dedikasi Anda. Anda telah sukses melakukan check-in dan check-out untuk hari ini.</p>
                        </div>

                        <!-- Summary Table -->
                        <div class="max-w-md mx-auto p-4 bg-gray-50/50 border border-gray-100 rounded-2xl text-left space-y-2.5 text-xs text-gray-600">
                            <div class="flex justify-between items-center py-1 border-b border-gray-100">
                                <span class="font-bold text-gray-500">JAM MASUK (CHECK-IN)</span>
                                <span class="font-extrabold text-gray-800">
                                    {{ \Carbon\Carbon::parse($attendance->check_in_at)->format('H:i') }} WIB
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-1 border-b border-gray-100">
                                <span class="font-bold text-gray-500">JAM PULANG (CHECK-OUT)</span>
                                <span class="font-extrabold text-gray-800">
                                    {{ \Carbon\Carbon::parse($attendance->check_out_at)->format('H:i') }} WIB
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-1">
                                <span class="font-bold text-gray-500">STATUS KEHADIRAN</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-50 border border-green-200 text-green-700">
                                    {{ $attendance->status->label() }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>

<script>
    // Live Client Clock Update for UI Responsiveness
    function updateClock() {
        const clockEl = document.getElementById('realtime-clock');
        if (clockEl) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            clockEl.textContent = `${hours}:${minutes}:${seconds}`;
        }
    }
    setInterval(updateClock, 1000);
    updateClock(); // run immediately
</script>

@script
<script>
    // Browser HTML5 Geolocation Watcher
    let watchId = null;

    function getGPSLocation() {
        if (!navigator.geolocation) {
            $wire.set('locationError', "Browser Anda tidak mendukung deteksi lokasi.");
            return;
        }

        const highAccuracyOptions = {
            enableHighAccuracy: true,
            timeout: 3000, // 3 seconds timeout
            maximumAge: 10000 // allow 10s old cached position
        };

        const lowAccuracyOptions = {
            enableHighAccuracy: false,
            timeout: 8000,
            maximumAge: 30000
        };

        function startWatching(options) {
            if (watchId) {
                navigator.geolocation.clearWatch(watchId);
            }
            watchId = navigator.geolocation.watchPosition(
                (position) => {
                    $wire.set('latitude', position.coords.latitude);
                    $wire.set('longitude', position.coords.longitude);
                    $wire.set('accuracy', position.coords.accuracy);
                    $wire.set('locationError', '');
                },
                (error) => {
                    if (options.enableHighAccuracy) {
                        console.warn("High accuracy GPS failed/timed out. Falling back to low accuracy Wifi/IP location...");
                        startWatching(lowAccuracyOptions);
                    } else {
                        let msg = '';
                        switch(error.code) {
                            case error.PERMISSION_DENIED:
                                msg = "Akses lokasi ditolak. Silakan izinkan akses lokasi pada browser Anda.";
                                break;
                            case error.POSITION_UNAVAILABLE:
                                msg = "Informasi lokasi tidak tersedia.";
                                break;
                            case error.TIMEOUT:
                                msg = "Waktu permintaan lokasi habis.";
                                break;
                            default:
                                msg = "Terjadi kesalahan saat mendeteksi lokasi.";
                        }
                        $wire.set('locationError', msg);
                    }
                },
                options
            );
        }

        // Start watching with high accuracy first, fallback if it takes longer than 3s
        startWatching(highAccuracyOptions);
    }

    getGPSLocation();

    // Clean up watcher on destroy
    document.addEventListener('livewire:navigating', () => {
        if (watchId) {
            navigator.geolocation.clearWatch(watchId);
        }
    });
</script>
@endscript
