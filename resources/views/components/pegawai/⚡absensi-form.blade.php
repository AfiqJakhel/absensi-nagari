<?php

use Livewire\Component;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Enums\AttendanceStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

new class extends Component
{
    public $photo = null;
    public $notes = '';

    private function savePhoto($base64Data, $typePrefix = 'photo')
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $typeMatch)) {
            $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            $ext = strtolower($typeMatch[1]);
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $ext = 'jpg'; // fallback
            }
            $base64Data = str_replace(' ', '+', $base64Data);
            $imageName = $typePrefix . '_' . time() . '_' . Str::random(10) . '.' . $ext;
            $path = 'attendances/' . $imageName;
            
            Storage::disk('public')->put($path, base64_decode($base64Data));
            return $path;
        }
        throw new \Exception('Data foto tidak valid.');
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

            // 4. Validate photo
            if (empty($this->photo)) {
                session()->flash('error', 'Anda wajib mengambil foto absensi terlebih dahulu.');
                return;
            }

            $photoPath = $this->savePhoto($this->photo, 'check_in');

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
                    'check_in_photo_path' => $photoPath,
                    'check_in_ip' => request()->ip(),
                    'check_in_user_agent' => request()->userAgent(),
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'notes' => $this->notes,
                ]
            );

            $this->notes = '';
            $this->photo = null;
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

            // 4. Validate photo
            if (empty($this->photo)) {
                session()->flash('error', 'Anda wajib mengambil foto absensi kepulangan terlebih dahulu.');
                return;
            }

            $photoPath = $this->savePhoto($this->photo, 'check_out');

            // 5. Update attendance for check-out
            $attendance->update([
                'check_out_at' => $now,
                'check_out_photo_path' => $photoPath,
                'check_out_ip' => request()->ip(),
                'check_out_user_agent' => request()->userAgent(),
            ]);

            $this->photo = null;
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

        if ($schedule) {
            $attendance = Attendance::where('user_id', $user->id)
                ->where('schedule_id', $schedule->id)
                ->whereDate('attendance_date', $schedule->attendance_date)
                ->first();
        }

        return [
            'schedule' => $schedule,
            'attendance' => $attendance,
        ];
    }
};
?>

<div class="max-w-5xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
    <div class="lg:col-span-2 space-y-6">
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
        
        <!-- Live Camera Area (Always Accessible) -->
        <div class="space-y-3 pb-4 border-b border-gray-100" x-data="cameraApp()" x-init="initCamera()">
            <div class="flex justify-between items-center ml-1">
                <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Tes & Tangkapan Kamera</h4>
                <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded font-bold" x-show="photoTaken" x-cloak>Tersimpan Sementara</span>
            </div>
            <div class="relative bg-gray-900 rounded-2xl overflow-hidden aspect-video border-2" :class="photoTaken ? 'border-emerald-500 shadow-md shadow-emerald-500/20' : 'border-gray-200'">
                <video x-ref="video" class="w-full h-full object-cover" autoplay playsinline x-show="!photoTaken"></video>
                <canvas x-ref="canvas" class="hidden"></canvas>
                <img x-ref="photoResult" class="w-full h-full object-cover" x-show="photoTaken" />
                
                <div class="absolute inset-0 flex items-center justify-center bg-gray-900/80" x-show="permissionDenied" x-cloak>
                    <div class="text-center text-white space-y-2 p-4">
                        <i class="fa-solid fa-camera-slash text-3xl text-red-500"></i>
                        <p class="text-sm font-bold">Kamera Gagal Diakses</p>
                        <p class="text-xs text-gray-300" x-text="errorMessage || 'Mohon izinkan akses kamera di pengaturan browser.'"></p>
                    </div>
                </div>
            </div>
            
            <div class="flex gap-2">
                <button type="button" @click="takePhoto" x-show="!photoTaken" :disabled="permissionDenied" class="w-full py-2.5 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold text-sm rounded-xl transition-colors disabled:opacity-50">
                    <i class="fa-solid fa-camera mr-1"></i> Ambil Foto
                </button>
                <button type="button" @click="retakePhoto" x-show="photoTaken" x-cloak class="w-full py-2.5 px-4 bg-amber-50 hover:bg-amber-100 text-amber-700 font-bold text-sm rounded-xl transition-colors">
                    <i class="fa-solid fa-rotate-right mr-1"></i> Foto Ulang
                </button>
            </div>
        </div>

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

                        @php
                            $now = Carbon::now();
                            $checkInStart = Carbon::parse($schedule->attendance_date->toDateString() . ' ' . $schedule->check_in_start);
                            $checkInEnd = Carbon::parse($schedule->attendance_date->toDateString() . ' ' . $schedule->check_in_end);
                            $timeValid = $now->between($checkInStart, $checkInEnd);
                            $canCheckIn = $timeValid && !empty($photo);
                        @endphp
                        

                        
                        <button wire:click="checkIn" @disabled(!$canCheckIn)
                            class="w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-200 disabled:text-gray-400 disabled:shadow-none transition-all duration-200 transform hover:-translate-y-0.5 disabled:pointer-events-none gap-2">
                            <i class="fa-solid fa-right-to-bracket"></i> Ambil Absen Masuk
                        </button>
                        
                        @if (!$timeValid)
                            <p class="text-center text-xs text-red-500 font-semibold mt-2">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Waktu absensi di luar batas check-in dibuka.
                            </p>
                        @elseif (empty($photo))
                            <p class="text-center text-xs text-amber-600 font-semibold mt-2">
                                <i class="fa-solid fa-camera mr-1"></i> Foto absensi belum diambil.
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

                        @php
                            $now = Carbon::now();
                            $checkOutStart = Carbon::parse($schedule->attendance_date->toDateString() . ' ' . $schedule->check_out_start);
                            $checkOutEnd = Carbon::parse($schedule->attendance_date->toDateString() . ' ' . $schedule->check_out_end);
                            $timeValidOut = $now->between($checkOutStart, $checkOutEnd);
                            $canCheckOut = $timeValidOut && !empty($photo);
                        @endphp
                        

                        
                        <button wire:click="checkOut" @disabled(!$canCheckOut)
                            class="w-full flex justify-center items-center py-4 px-6 border border-transparent rounded-xl shadow-lg text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 disabled:bg-gray-200 disabled:text-gray-400 disabled:shadow-none transition-all duration-200 transform hover:-translate-y-0.5 disabled:pointer-events-none gap-2">
                            <i class="fa-solid fa-right-from-bracket"></i> Ambil Absen Pulang
                        </button>
                        
                        @if (!$timeValidOut)
                            <p class="text-center text-xs text-red-500 font-semibold mt-2">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Waktu check-out belum dibuka / sudah ditutup. Jam pulang dimulai pukul {{ \Carbon\Carbon::parse($schedule->check_out_start)->format('H:i') }} WIB.
                            </p>
                        @elseif (empty($photo))
                            <p class="text-center text-xs text-amber-600 font-semibold mt-2">
                                <i class="fa-solid fa-camera mr-1"></i> Foto absensi kepulangan belum diambil.
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
    
    <!-- Right Column: Tutorial -->
    <div class="lg:col-span-1 space-y-6 sticky top-6">
        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm space-y-5">
            <div>
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-blue-500"></i>
                    Panduan Absensi
                </h3>
                <p class="text-xs text-gray-500 mt-1">Ikuti langkah berikut untuk merekam kehadiran dengan benar.</p>
            </div>
            
            <div class="space-y-4 relative before:absolute before:inset-0 before:ml-3.5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-300 before:to-transparent">
                
                <!-- Step 1 -->
                <div class="relative flex items-start gap-4">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold text-xs ring-4 ring-white z-10 shrink-0">1</div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 w-full">
                        <h4 class="text-xs font-bold text-gray-800">Cek Waktu</h4>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">Pastikan Anda absen pada jadwal buka. Absen akan ditutup jika di luar batas jam.</p>
                    </div>
                </div>

                <!-- Step 2 -->
                <div class="relative flex items-start gap-4">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 font-bold text-xs ring-4 ring-white z-10 shrink-0">2</div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 w-full">
                        <h4 class="text-xs font-bold text-gray-800">Izinkan Kamera</h4>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">Browser akan meminta izin kamera. Pastikan Anda klik <span class="font-bold text-gray-700">Allow</span> atau <span class="font-bold text-gray-700">Izinkan</span>.</p>
                    </div>
                </div>

                <!-- Step 3 -->
                <div class="relative flex items-start gap-4">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-600 font-bold text-xs ring-4 ring-white z-10 shrink-0">3</div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 w-full">
                        <h4 class="text-xs font-bold text-gray-800">Ambil Foto Wajah</h4>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">Posisikan wajah di kamera, lalu klik tombol abu-abu <span class="font-bold text-gray-700">Ambil Foto</span>.</p>
                    </div>
                </div>

                <!-- Step 4 -->
                <div class="relative flex items-start gap-4">
                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-bold text-xs ring-4 ring-white z-10 shrink-0">4</div>
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 w-full">
                        <h4 class="text-xs font-bold text-gray-800">Kirim Absensi</h4>
                        <p class="text-[11px] text-gray-500 mt-1 leading-relaxed">Klik tombol <span class="font-bold text-gray-700">Absen Masuk</span> atau <span class="font-bold text-gray-700">Absen Pulang</span> untuk merekam kehadiran.</p>
                    </div>
                </div>
            </div>
            
            <div class="p-3 bg-red-50 border border-red-100 rounded-xl">
                <p class="text-[11px] text-red-700 font-semibold leading-relaxed">
                    <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                    Sistem merekam data waktu dan gambar secara real-time. Dilarang melakukan kecurangan.
                </p>
            </div>
        </div>
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

<script>
    function cameraApp() {
        return {
            stream: null,
            photoTaken: false,
            permissionDenied: false,
            errorMessage: '',
            initCamera() {
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    // Try with specific facing mode first
                    navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } })
                        .then(stream => {
                            this.stream = stream;
                            this.$refs.video.srcObject = stream;
                        })
                        .catch(err => {
                            console.warn("User facing mode failed, trying default camera...", err);
                            // Fallback to any available camera (fixes OverconstrainedError on PC)
                            navigator.mediaDevices.getUserMedia({ video: true })
                                .then(stream => {
                                    this.stream = stream;
                                    this.$refs.video.srcObject = stream;
                                })
                                .catch(fallbackErr => {
                                    console.error("Camera access totally denied:", fallbackErr);
                                    this.permissionDenied = true;
                                    this.errorMessage = fallbackErr.name + ": " + fallbackErr.message;
                                });
                        });
                } else {
                    this.permissionDenied = true;
                    this.errorMessage = "Browser Anda tidak mendukung akses kamera (HTTPS diperlukan).";
                }
            },
            takePhoto() {
                const video = this.$refs.video;
                const canvas = this.$refs.canvas;
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                this.$refs.photoResult.src = dataUrl;
                this.photoTaken = true;
                this.$wire.set('photo', dataUrl);
                // Matikan sementara stream kamera utama jika mau
                // video.pause(); 
            },
            retakePhoto() {
                this.photoTaken = false;
                this.$wire.set('photo', null);
                this.$refs.photoResult.src = '';
                // this.$refs.video.play();
            }
        }
    }
</script>
