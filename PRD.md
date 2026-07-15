# PRODUCT REQUIREMENTS DOCUMENT

## Sistem Informasi Absensi Berbasis Web

### 1. Informasi Produk

**Nama produk:** Attendify
**Jenis produk:** Website absensi
**Platform:** Web responsive
**Framework utama:** Laravel full-stack
**Target pengguna:** Perangkat Nagari Kambang
**Versi awal:** MVP 1.0

---

## 2. Instruksi Utama untuk Coding Agent

Bangun aplikasi absensi berbasis web menggunakan satu project Laravel full-stack.

Gunakan teknologi berikut:

* Laravel
* Blade
* Livewire
* Tailwind CSS
* Laravel Starter Kit
* Authentication berbasis session dan cookie
* MySQL
* Eloquent ORM
* Pest atau PHPUnit
* Vite

Jangan gunakan:

* JWT untuk autentikasi web
* React
* Vue
* Next.js
* Express.js
* Backend dan frontend terpisah
* API terpisah untuk fitur utama MVP

Seluruh tampilan, autentikasi, proses bisnis, validasi, dan pengelolaan database harus berada dalam satu project Laravel.

Gunakan struktur kode yang modular, konsisten, mudah dirawat, dan mengikuti standar Laravel.

---

# 3. Latar Belakang

Proses absensi manual menggunakan kertas, spreadsheet, atau pesan pribadi sering menyebabkan beberapa masalah:

* Data kehadiran mudah hilang atau berubah.
* Rekapitulasi membutuhkan waktu lama.
* Sulit mengetahui siapa yang terlambat atau tidak hadir.
* Pengguna dapat melakukan absensi di luar jadwal.
* Tidak tersedia riwayat aktivitas yang jelas.
* Admin kesulitan menghasilkan laporan harian dan bulanan.

Sistem ini dibuat untuk membantu organisasi mengelola absensi secara terpusat, cepat, aman, dan mudah digunakan.

---

# 4. Tujuan Produk

Sistem harus mampu:

1. Mengelola pengguna dan hak akses.
2. Mengelola jadwal absensi.
3. Mencatat check-in dan check-out.
4. Menentukan status kehadiran secara otomatis.
5. Menyimpan lokasi dan waktu absensi.
6. Menampilkan riwayat absensi pengguna.
7. Menyediakan rekap absensi kepada admin.
8. Menghasilkan laporan berdasarkan periode.
9. Mencegah absensi ganda.
10. Menyediakan audit aktivitas penting.

---

# 5. Ruang Lingkup MVP

Versi MVP mencakup:

* Login dan logout
* Pengelolaan akun pengguna
* Pengelolaan role
* Pengelolaan jadwal
* Check-in
* Check-out
* Status hadir
* Status terlambat
* Status izin
* Status sakit
* Status alfa
* Riwayat absensi
* Rekap absensi
* Dashboard admin
* Dashboard pengguna
* Pengajuan izin atau sakit
* Persetujuan pengajuan
* Filter laporan
* Export Excel
* Responsive mobile dan desktop

Fitur berikut tidak termasuk dalam MVP:

* Face recognition
* Fingerprint
* Integrasi mesin absensi
* Aplikasi Android atau iOS
* Payroll
* Integrasi WhatsApp
* Integrasi SSO
* Multi-tenant
* JWT
* Microservices

Fitur tersebut dapat dikembangkan setelah MVP selesai.

---

# 6. Jenis Pengguna

## 6.1 Admin

Admin memiliki akses penuh terhadap sistem.

Admin dapat:

* Melihat dashboard keseluruhan.
* Mengelola pengguna.
* Mengelola role.
* Mengelola divisi atau unit.
* Mengelola jadwal.
* Melihat seluruh data absensi.
* Memperbaiki data absensi dengan alasan yang tercatat.
* Menyetujui atau menolak pengajuan izin.
* Menyetujui atau menolak pengajuan sakit.
* Mengunduh laporan.
* Melihat audit log.

## 6.2 Operator

Operator membantu admin mengelola kegiatan absensi.

Operator dapat:

* Mengelola jadwal.
* Melihat data absensi.
* Memverifikasi pengajuan izin.
* Melihat laporan.
* Mengunduh laporan.

Operator tidak boleh:

* Menghapus admin.
* Mengubah konfigurasi sistem utama.
* Mengubah role admin.
* Melihat informasi sensitif yang tidak diperlukan.

## 6.3 Pengguna

Pengguna adalah mahasiswa, pegawai, anggota organisasi, atau peserta.

Pengguna dapat:

* Login.
* Melihat jadwal.
* Melakukan check-in.
* Melakukan check-out.
* Melihat status kehadiran.
* Melihat riwayat absensi sendiri.
* Mengajukan izin.
* Mengajukan sakit.
* Mengunggah bukti pendukung.
* Mengubah profil sendiri.
* Mengubah password.

---

# 7. Role dan Permission

Gunakan role berikut:

* `admin`
* `operator`
* `user`

Gunakan enum atau package permission yang sesuai.

Rekomendasi:

* Spatie Laravel Permission

Setiap route dan action wajib dilindungi menggunakan middleware dan authorization policy.

Contoh:

* Hanya admin yang dapat menghapus pengguna.
* Admin dan operator dapat melihat seluruh absensi.
* Pengguna hanya dapat melihat absensinya sendiri.
* Pengguna tidak dapat menyetujui pengajuan izin sendiri.
* Pengguna tidak dapat mengubah jadwal.

---

# 8. Autentikasi

Gunakan Laravel Starter Kit dengan mekanisme session dan cookie.

Fitur autentikasi:

* Register dapat dinonaktifkan.
* Login.
* Logout.
* Lupa password.
* Reset password.
* Verifikasi email opsional.
* Ubah password.
* Remember me.
* Rate limiting login.
* Regenerasi session setelah login.
* Invalidasi session setelah logout.

Untuk lingkungan organisasi, akun pengguna sebaiknya dibuat oleh admin.

Jika fitur register dinonaktifkan, halaman register tidak boleh dapat diakses oleh publik.

Jangan gunakan JWT untuk autentikasi website MVP.

---

# 9. Modul Utama

## 9.1 Modul Dashboard

### Dashboard Admin

Tampilkan informasi:

* Total pengguna aktif.
* Jumlah hadir hari ini.
* Jumlah terlambat hari ini.
* Jumlah izin hari ini.
* Jumlah sakit hari ini.
* Jumlah alfa hari ini.
* Jumlah belum check-in.
* Jumlah pengajuan menunggu persetujuan.
* Grafik kehadiran tujuh hari terakhir.
* Grafik status kehadiran bulan berjalan.
* Daftar absensi terbaru.
* Daftar pengajuan terbaru.

### Dashboard Pengguna

Tampilkan informasi:

* Nama pengguna.
* Jadwal hari ini.
* Status absensi hari ini.
* Jam check-in.
* Jam check-out.
* Status hadir atau terlambat.
* Tombol check-in.
* Tombol check-out.
* Ringkasan kehadiran bulan berjalan.
* Riwayat absensi terbaru.
* Pengajuan izin terbaru.

---

## 9.2 Modul Pengguna

Admin dapat:

* Menambah pengguna.
* Melihat daftar pengguna.
* Mencari pengguna.
* Memfilter pengguna.
* Mengubah data pengguna.
* Mengaktifkan pengguna.
* Menonaktifkan pengguna.
* Mereset password pengguna.
* Menentukan role.
* Menentukan divisi.
* Menghapus pengguna jika tidak memiliki data penting.

Data pengguna:

* Nama
* Email
* Nomor identitas
* Nomor telepon
* Role
* Divisi
* Status aktif
* Foto profil
* Password

Aturan:

* Email harus unik.
* Nomor identitas harus unik.
* Password minimal delapan karakter.
* Pengguna nonaktif tidak boleh login.
* Admin terakhir tidak boleh dihapus.
* Pengguna yang memiliki data absensi sebaiknya dinonaktifkan, bukan dihapus.

---

## 9.3 Modul Divisi

Admin dapat mengelola:

* Nama divisi
* Kode divisi
* Deskripsi
* Status aktif

Contoh divisi:

* Teknologi Informasi
* Keuangan
* Sumber Daya Manusia
* Operasional
* Akademik

Setiap pengguna dapat memiliki satu divisi.

---

## 9.4 Modul Jadwal

Admin dan operator dapat:

* Menambah jadwal.
* Mengubah jadwal.
* Menghapus jadwal yang belum digunakan.
* Mengaktifkan atau menonaktifkan jadwal.
* Menetapkan jadwal kepada pengguna.
* Menetapkan jadwal kepada divisi.
* Menentukan jam masuk.
* Menentukan jam pulang.
* Menentukan batas keterlambatan.
* Menentukan waktu check-in dibuka.
* Menentukan waktu check-in ditutup.
* Menentukan waktu check-out dibuka.
* Menentukan waktu check-out ditutup.

Data jadwal:

* Nama jadwal
* Tanggal
* Jam masuk
* Jam pulang
* Check-in mulai
* Check-in berakhir
* Check-out mulai
* Check-out berakhir
* Batas toleransi keterlambatan
* Lokasi
* Radius lokasi
* Status aktif
* Catatan

Contoh:

* Nama jadwal: Jadwal Reguler
* Jam masuk: 08.00
* Jam pulang: 16.00
* Check-in mulai: 07.00
* Check-in berakhir: 09.00
* Toleransi keterlambatan: 15 menit

---

## 9.5 Modul Absensi

Pengguna dapat melakukan:

* Check-in
* Check-out

Data yang disimpan:

* Pengguna
* Jadwal
* Tanggal
* Waktu check-in
* Waktu check-out
* Latitude check-in
* Longitude check-in
* Latitude check-out
* Longitude check-out
* Status
* Jumlah menit terlambat
* Catatan
* Perangkat atau user agent
* Alamat IP
* Waktu pembuatan data
* Waktu perubahan data

Status absensi:

* `present`
* `late`
* `permission`
* `sick`
* `absent`

Label bahasa Indonesia:

* Hadir
* Terlambat
* Izin
* Sakit
* Alfa

---

# 10. Aturan Bisnis Absensi

## 10.1 Check-in

Pengguna dapat check-in jika:

* Sudah login.
* Akun aktif.
* Memiliki jadwal pada hari tersebut.
* Jadwal aktif.
* Waktu saat ini berada dalam periode check-in.
* Belum pernah check-in pada jadwal yang sama.
* Lokasi berada dalam radius yang diizinkan jika validasi lokasi diaktifkan.

Sistem harus menggunakan waktu server.

Jangan mempercayai waktu dari perangkat pengguna.

Jika check-in dilakukan sebelum atau tepat pada batas toleransi, status:

`present`

Jika check-in dilakukan setelah batas toleransi tetapi masih dalam periode check-in, status:

`late`

Rumus keterlambatan:

```text
late_minutes = waktu_check_in - batas_waktu_hadir
```

Nilai tidak boleh negatif.

## 10.2 Check-out

Pengguna dapat check-out jika:

* Sudah melakukan check-in.
* Belum melakukan check-out.
* Waktu sudah memasuki periode check-out.
* Jadwal masih berlaku.

Check-out tidak boleh dilakukan dua kali.

## 10.3 Absensi Ganda

Tambahkan unique constraint pada kombinasi:

```text
user_id + schedule_id + attendance_date
```

Sistem wajib mencegah data absensi ganda pada level aplikasi dan database.

## 10.4 Alfa

Pengguna dianggap alfa jika:

* Memiliki jadwal.
* Tidak melakukan check-in.
* Tidak memiliki pengajuan izin atau sakit yang disetujui.
* Periode absensi telah berakhir.

Penandaan alfa dilakukan melalui Laravel Scheduler.

## 10.5 Perubahan Data Absensi

Admin dapat memperbaiki data absensi.

Setiap perubahan wajib menyimpan:

* Admin yang mengubah.
* Data sebelum perubahan.
* Data setelah perubahan.
* Alasan perubahan.
* Waktu perubahan.

---

# 11. Validasi Lokasi

Sistem dapat menggunakan geolocation browser.

Data lokasi:

* Latitude
* Longitude
* Accuracy
* Timestamp

Admin dapat menentukan:

* Latitude lokasi utama.
* Longitude lokasi utama.
* Radius maksimal dalam meter.

Gunakan perhitungan Haversine untuk menentukan jarak pengguna dengan lokasi absensi.

Absensi ditolak jika:

```text
jarak_pengguna > radius_yang_diizinkan
```

Sistem harus memberikan pesan yang jelas jika:

* Pengguna menolak izin lokasi.
* Browser tidak mendukung geolocation.
* Akurasi lokasi terlalu rendah.
* Pengguna berada di luar radius.

Validasi lokasi dapat diaktifkan atau dinonaktifkan pada setiap jadwal.

---

# 12. Modul Pengajuan Izin dan Sakit

Pengguna dapat membuat pengajuan dengan data:

* Jenis pengajuan
* Tanggal mulai
* Tanggal selesai
* Alasan
* File bukti
* Status
* Catatan verifikator

Jenis pengajuan:

* Izin
* Sakit

Status pengajuan:

* Menunggu
* Disetujui
* Ditolak

Aturan:

* Tanggal selesai tidak boleh sebelum tanggal mulai.
* Pengguna tidak boleh membuat pengajuan ganda pada tanggal yang sama.
* File bukti bersifat opsional untuk izin.
* File bukti dapat diwajibkan untuk sakit.
* File yang diperbolehkan: PDF, JPG, JPEG, PNG.
* Ukuran maksimal file: 5 MB.
* Pengguna hanya dapat mengubah pengajuan berstatus menunggu.
* Pengajuan yang disetujui mengubah status absensi menjadi izin atau sakit.
* Pengajuan yang sudah disetujui tidak boleh dihapus oleh pengguna.

---

# 13. Modul Laporan

Admin dan operator dapat melihat laporan berdasarkan:

* Tanggal
* Rentang tanggal
* Bulan
* Tahun
* Pengguna
* Divisi
* Jadwal
* Status kehadiran

Data laporan:

* Nomor
* Nama pengguna
* Nomor identitas
* Divisi
* Tanggal
* Jadwal
* Check-in
* Check-out
* Status
* Menit terlambat
* Catatan

Fitur:

* Pencarian
* Pagination
* Filter
* Sorting
* Export Excel
* Ringkasan jumlah status
* Persentase kehadiran

Rumus persentase kehadiran:

```text
jumlah hadir dan terlambat
dibagi
total jadwal
dikali 100 persen
```

Status izin dan sakit tidak dihitung sebagai hadir, tetapi dapat ditampilkan terpisah.

---

# 14. Halaman yang Dibutuhkan

## Halaman Publik

* Login
* Lupa password
* Reset password

## Halaman Pengguna

* Dashboard
* Absensi hari ini
* Riwayat absensi
* Detail absensi
* Jadwal saya
* Daftar pengajuan
* Buat pengajuan
* Detail pengajuan
* Profil
* Ubah password

## Halaman Admin

* Dashboard admin
* Daftar pengguna
* Tambah pengguna
* Edit pengguna
* Detail pengguna
* Daftar divisi
* Tambah divisi
* Edit divisi
* Daftar jadwal
* Tambah jadwal
* Edit jadwal
* Detail jadwal
* Daftar absensi
* Detail absensi
* Edit absensi
* Daftar pengajuan
* Detail pengajuan
* Laporan
* Audit log
* Pengaturan sistem

---

# 15. Navigasi

## Sidebar Admin

* Dashboard
* Pengguna
* Divisi
* Jadwal
* Absensi
* Pengajuan
* Laporan
* Audit Log
* Pengaturan

## Sidebar Pengguna

* Dashboard
* Absensi
* Jadwal Saya
* Riwayat
* Pengajuan
* Profil

Sidebar harus responsive.

Pada perangkat mobile, gunakan drawer atau menu hamburger.

---

# 16. Desain Antarmuka

Gunakan desain:

* Bersih
* Modern
* Profesional
* Responsive
* Mudah dipahami
* Konsisten
* Mobile-first

Gunakan Tailwind CSS.

Komponen yang diperlukan:

* Sidebar
* Navbar
* Card statistik
* Table
* Pagination
* Modal
* Form input
* Select
* Date picker
* Time input
* Badge status
* Alert
* Toast notification
* Confirmation dialog
* Empty state
* Loading state
* Skeleton
* Dropdown profile

Warna status:

* Hadir: hijau
* Terlambat: kuning atau oranye
* Izin: biru
* Sakit: ungu
* Alfa: merah
* Menunggu: abu-abu atau kuning
* Ditolak: merah
* Disetujui: hijau

Pastikan setiap warna tetap memiliki label teks agar tidak bergantung hanya pada warna.

---

# 17. Struktur Database

## 17.1 Tabel `users`

Kolom:

* id
* name
* email
* email_verified_at
* password
* employee_number
* phone
* profile_photo
* division_id
* is_active
* remember_token
* created_at
* updated_at
* deleted_at

Constraint:

* email unique
* employee_number unique
* division_id foreign key nullable

## 17.2 Tabel `divisions`

Kolom:

* id
* name
* code
* description
* is_active
* created_at
* updated_at
* deleted_at

Constraint:

* code unique

## 17.3 Tabel `schedules`

Kolom:

* id
* name
* attendance_date
* start_time
* end_time
* check_in_start
* check_in_end
* check_out_start
* check_out_end
* late_tolerance_minutes
* location_name
* latitude
* longitude
* radius_meters
* location_validation_enabled
* is_active
* notes
* created_by
* created_at
* updated_at
* deleted_at

## 17.4 Tabel `schedule_user`

Kolom:

* id
* schedule_id
* user_id
* created_at
* updated_at

Constraint:

* kombinasi schedule_id dan user_id harus unik

## 17.5 Tabel `attendances`

Kolom:

* id
* user_id
* schedule_id
* attendance_date
* check_in_at
* check_out_at
* check_in_latitude
* check_in_longitude
* check_in_accuracy
* check_out_latitude
* check_out_longitude
* check_out_accuracy
* check_in_ip
* check_out_ip
* check_in_user_agent
* check_out_user_agent
* status
* late_minutes
* notes
* created_at
* updated_at
* deleted_at

Constraint:

* user_id foreign key
* schedule_id foreign key
* kombinasi user_id, schedule_id, attendance_date harus unik

## 17.6 Tabel `leave_requests`

Kolom:

* id
* user_id
* type
* start_date
* end_date
* reason
* attachment
* status
* reviewed_by
* reviewed_at
* reviewer_notes
* created_at
* updated_at
* deleted_at

## 17.7 Tabel `attendance_adjustments`

Kolom:

* id
* attendance_id
* changed_by
* previous_data
* updated_data
* reason
* created_at
* updated_at

Gunakan tipe JSON untuk:

* previous_data
* updated_data

## 17.8 Tabel `audit_logs`

Kolom:

* id
* user_id
* action
* module
* model_type
* model_id
* old_values
* new_values
* ip_address
* user_agent
* created_at

---

# 18. Relasi Model

Relasi utama:

* Division memiliki banyak User.
* User dimiliki oleh satu Division.
* User memiliki banyak Schedule melalui pivot.
* Schedule memiliki banyak User melalui pivot.
* User memiliki banyak Attendance.
* Schedule memiliki banyak Attendance.
* User memiliki banyak LeaveRequest.
* Attendance memiliki banyak AttendanceAdjustment.
* User memiliki banyak AuditLog.

Gunakan eager loading untuk menghindari masalah N+1 query.

---

# 19. Enum

Gunakan PHP Enum untuk status yang memiliki nilai tetap.

## AttendanceStatus

```php
enum AttendanceStatus: string
{
    case PRESENT = 'present';
    case LATE = 'late';
    case PERMISSION = 'permission';
    case SICK = 'sick';
    case ABSENT = 'absent';
}
```

## LeaveRequestStatus

```php
enum LeaveRequestStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
```

## LeaveRequestType

```php
enum LeaveRequestType: string
{
    case PERMISSION = 'permission';
    case SICK = 'sick';
}
```

Gunakan casting enum pada model.

---

# 20. Service Class

Hindari meletakkan seluruh logika bisnis di controller atau komponen Livewire.

Buat service berikut:

* `AttendanceService`
* `ScheduleService`
* `LeaveRequestService`
* `LocationService`
* `ReportService`
* `AuditLogService`

Contoh tanggung jawab `AttendanceService`:

* Memvalidasi jadwal pengguna.
* Memvalidasi waktu check-in.
* Menentukan status hadir atau terlambat.
* Menghitung menit keterlambatan.
* Membuat data check-in.
* Memproses check-out.
* Mencegah absensi ganda.

Contoh tanggung jawab `LocationService`:

* Menghitung jarak Haversine.
* Memvalidasi radius.
* Memvalidasi akurasi lokasi.

---

# 21. Form Request dan Validasi

Gunakan Form Request untuk validasi form kompleks.

Buat:

* `StoreUserRequest`
* `UpdateUserRequest`
* `StoreDivisionRequest`
* `UpdateDivisionRequest`
* `StoreScheduleRequest`
* `UpdateScheduleRequest`
* `StoreLeaveRequest`
* `ReviewLeaveRequest`
* `UpdateAttendanceRequest`

Pesan validasi harus tersedia dalam bahasa Indonesia.

Contoh:

* Email sudah digunakan.
* Jadwal tidak ditemukan.
* Anda sudah melakukan check-in.
* Waktu check-in belum dimulai.
* Periode check-in telah berakhir.
* Lokasi Anda berada di luar radius absensi.
* Anda belum dapat melakukan check-out.
* File bukti maksimal 5 MB.

---

# 22. Routes

Gunakan named routes.

Contoh kelompok route:

```php
Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/attendance', AttendanceToday::class)
        ->name('attendance.today');

    Route::get('/attendance/history', AttendanceHistory::class)
        ->name('attendance.history');

    Route::get('/leave-requests', LeaveRequestIndex::class)
        ->name('leave-requests.index');
});
```

Route admin:

```php
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'active', 'role:admin|operator'])
    ->group(function () {
        // Admin routes
    });
```

Gunakan middleware:

* auth
* verified jika verifikasi email digunakan
* active
* role
* permission

---

# 23. Authorization

Gunakan Policy untuk:

* User
* Schedule
* Attendance
* LeaveRequest
* Division

Contoh aturan:

* Pengguna hanya dapat melihat attendance miliknya.
* Admin dapat melihat seluruh attendance.
* Operator dapat melihat attendance sesuai izin.
* Pengguna hanya dapat mengubah leave request berstatus pending.
* Pengguna tidak dapat meninjau leave request sendiri.

Jangan hanya menyembunyikan tombol pada frontend.

Semua authorization harus divalidasi kembali pada backend.

---

# 24. Laravel Scheduler

Gunakan Laravel Scheduler untuk:

## Menandai Alfa

Jalankan setiap hari setelah periode absensi selesai.

Proses:

1. Ambil jadwal aktif yang sudah berakhir.
2. Ambil seluruh pengguna pada jadwal tersebut.
3. Periksa data absensi.
4. Periksa pengajuan izin atau sakit yang disetujui.
5. Jika tidak ada data, buat attendance dengan status alfa.

## Pengingat Check-out

Opsional untuk versi berikutnya.

## Pembersihan File Sementara

Hapus file sementara yang tidak terpakai.

Pastikan command bersifat idempotent.

Command tidak boleh membuat data alfa ganda.

---

# 25. Seeder

Buat seeder awal:

## AdminSeeder

Data contoh:

* Nama: Administrator
* Email: [admin@example.com](mailto:admin@example.com)
* Password: password
* Role: admin
* Status: aktif

## RolePermissionSeeder

Buat role:

* admin
* operator
* user

Buat permission yang sesuai.

## DivisionSeeder

Buat beberapa divisi contoh.

## DemoUserSeeder

Buat pengguna dummy untuk pengujian.

Jangan gunakan password default yang lemah pada production.

Password default hanya untuk development.

---

# 26. Factory

Buat factory untuk:

* User
* Division
* Schedule
* Attendance
* LeaveRequest

Factory harus dapat digunakan untuk testing dan demo data.

---

# 27. Audit Log

Catat aktivitas penting:

* Login
* Logout
* Tambah pengguna
* Ubah pengguna
* Nonaktifkan pengguna
* Tambah jadwal
* Ubah jadwal
* Hapus jadwal
* Check-in
* Check-out
* Perubahan absensi
* Pengajuan izin
* Persetujuan pengajuan
* Penolakan pengajuan
* Export laporan

Audit log tidak boleh dapat diubah oleh pengguna biasa.

---

# 28. Keamanan

Implementasikan:

* CSRF protection
* Session regeneration
* Password hashing
* Rate limiting
* Authorization policy
* Middleware role
* Validasi input
* Sanitasi nama file
* Validasi tipe file
* Validasi ukuran file
* Pencegahan mass assignment
* Query menggunakan Eloquent atau Query Builder
* Jangan menggunakan raw query tanpa binding
* Jangan menyimpan password dalam plain text
* Jangan menyimpan secret di source code
* Gunakan `.env`
* Batasi akses storage
* Gunakan signed URL jika diperlukan
* Catat perubahan data penting

Pastikan file `.env` tidak masuk Git.

---

# 29. Penyimpanan File

File bukti pengajuan disimpan pada:

```text
storage/app/public/leave-requests
```

Gunakan:

```bash
php artisan storage:link
```

Nama file harus dibuat ulang oleh sistem.

Jangan menggunakan nama asli file sebagai nama penyimpanan utama.

Simpan nama asli file dalam database jika diperlukan.

---

# 30. Timezone

Gunakan timezone:

```text
Asia/Jakarta
```

Seluruh proses absensi harus menggunakan timezone aplikasi.

Simpan waktu secara konsisten.

Tampilkan waktu dengan format:

```text
d M Y, H:i
```

Contoh:

```text
15 Juli 2026, 08:15
```

---

# 31. Format Bahasa

Bahasa utama aplikasi:

```text
Bahasa Indonesia
```

Gunakan istilah konsisten:

* Check-in
* Check-out
* Hadir
* Terlambat
* Izin
* Sakit
* Alfa
* Jadwal
* Pengajuan
* Laporan
* Pengguna
* Divisi

Pesan error harus mudah dipahami dan tidak menampilkan stack trace kepada pengguna.

---

# 32. Notifikasi

MVP menggunakan:

* Flash message
* Toast notification
* Database notification opsional

Contoh notifikasi:

* Check-in berhasil.
* Check-out berhasil.
* Pengajuan berhasil dikirim.
* Pengajuan telah disetujui.
* Pengajuan telah ditolak.
* Jadwal berhasil ditambahkan.
* Data pengguna berhasil diperbarui.

---

# 33. Testing

Buat feature test untuk:

## Autentikasi

* Pengguna aktif dapat login.
* Pengguna nonaktif tidak dapat login.
* Pengguna dapat logout.
* Route protected tidak dapat dibuka tanpa login.

## Check-in

* Pengguna dapat check-in dalam jadwal.
* Pengguna tidak dapat check-in dua kali.
* Pengguna tidak dapat check-in sebelum jadwal.
* Pengguna tidak dapat check-in setelah periode berakhir.
* Status terlambat dihitung dengan benar.
* Pengguna di luar radius ditolak.

## Check-out

* Pengguna dapat check-out.
* Pengguna tidak dapat check-out sebelum check-in.
* Pengguna tidak dapat check-out dua kali.

## Authorization

* Pengguna tidak dapat melihat absensi orang lain.
* Operator tidak dapat menghapus admin.
* Admin dapat mengelola jadwal.
* Pengguna tidak dapat mengakses halaman admin.

## Pengajuan

* Pengguna dapat membuat pengajuan.
* Pengajuan dengan tanggal tidak valid ditolak.
* Pengguna tidak dapat mengubah pengajuan yang telah disetujui.
* Admin dapat menyetujui pengajuan.
* Pengajuan yang disetujui memengaruhi status absensi.

## Laporan

* Filter tanggal bekerja.
* Filter status bekerja.
* Export hanya dapat dilakukan admin dan operator.

Target minimum:

* Seluruh business rule penting memiliki test.
* Semua test harus lulus sebelum fitur dianggap selesai.

---

# 34. Non-Functional Requirements

## Performa

* Halaman utama dimuat dengan cepat.
* Gunakan pagination untuk tabel besar.
* Gunakan eager loading.
* Hindari query N+1.
* Tambahkan index pada kolom filter.
* Jangan mengambil seluruh data tanpa batas.

## Responsivitas

Aplikasi harus dapat digunakan pada:

* Desktop
* Laptop
* Tablet
* Smartphone

## Aksesibilitas

* Form memiliki label.
* Tombol memiliki teks yang jelas.
* Kontras warna cukup.
* Navigasi dapat digunakan dengan keyboard.
* Error form ditampilkan dekat input.
* Jangan hanya mengandalkan warna.

## Maintainability

* Gunakan service class.
* Gunakan Form Request.
* Gunakan Policy.
* Gunakan Enum.
* Gunakan named route.
* Gunakan reusable component.
* Hindari duplikasi kode.

---

# 35. Index Database

Tambahkan index pada:

* users.email
* users.employee_number
* users.division_id
* users.is_active
* schedules.attendance_date
* schedules.is_active
* attendances.user_id
* attendances.schedule_id
* attendances.attendance_date
* attendances.status
* leave_requests.user_id
* leave_requests.status
* leave_requests.start_date
* leave_requests.end_date

Tambahkan unique index untuk:

* users.email
* users.employee_number
* divisions.code
* schedule_user(schedule_id, user_id)
* attendances(user_id, schedule_id, attendance_date)

---

# 36. Konfigurasi Environment

Gunakan konfigurasi dasar:

```env
APP_NAME="Sistem Absensi"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=absensi_db
DB_USERNAME=root
DB_PASSWORD=
```

Jangan commit file `.env`.

Sediakan `.env.example`.

---

# 37. Struktur Folder Tambahan

Gunakan struktur:

```text
app/
├── Enums/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Livewire/
│   ├── Admin/
│   └── User/
├── Models/
├── Policies/
├── Services/
└── Support/
```

Struktur view:

```text
resources/views/
├── components/
├── layouts/
├── livewire/
│   ├── admin/
│   └── user/
└── partials/
```

---

# 38. Komponen Livewire

Buat komponen utama:

## Pengguna

* `Admin/User/Index`
* `Admin/User/Create`
* `Admin/User/Edit`
* `Admin/User/Show`

## Divisi

* `Admin/Division/Index`
* `Admin/Division/Form`

## Jadwal

* `Admin/Schedule/Index`
* `Admin/Schedule/Create`
* `Admin/Schedule/Edit`
* `Admin/Schedule/Show`

## Absensi

* `User/Attendance/Today`
* `User/Attendance/History`
* `Admin/Attendance/Index`
* `Admin/Attendance/Show`
* `Admin/Attendance/Edit`

## Pengajuan

* `User/LeaveRequest/Index`
* `User/LeaveRequest/Create`
* `User/LeaveRequest/Show`
* `Admin/LeaveRequest/Index`
* `Admin/LeaveRequest/Show`

## Laporan

* `Admin/Report/AttendanceReport`

Gunakan pagination Livewire untuk tabel.

Gunakan query string untuk filter agar filter tetap tersimpan saat halaman direfresh.

---

# 39. Acceptance Criteria MVP

MVP dianggap selesai jika:

1. Admin dapat login.
2. Admin dapat membuat akun pengguna.
3. Admin dapat membuat divisi.
4. Admin dapat membuat jadwal.
5. Admin dapat menetapkan pengguna ke jadwal.
6. Pengguna dapat login.
7. Pengguna dapat melihat jadwal hari ini.
8. Pengguna dapat check-in.
9. Sistem menentukan status hadir atau terlambat.
10. Pengguna tidak dapat check-in dua kali.
11. Pengguna dapat check-out.
12. Sistem menyimpan waktu check-in dan check-out.
13. Pengguna dapat melihat riwayat sendiri.
14. Pengguna dapat mengajukan izin atau sakit.
15. Admin dapat menyetujui atau menolak pengajuan.
16. Admin dapat melihat seluruh absensi.
17. Admin dapat memfilter laporan.
18. Admin dapat export laporan ke Excel.
19. Route admin tidak dapat dibuka pengguna biasa.
20. Data alfa dapat dibuat otomatis.
21. Semua fitur penting memiliki validasi.
22. Semua test utama berhasil.
23. Tampilan dapat digunakan pada perangkat mobile.
24. Tidak terdapat error pada browser console.
25. Tidak terdapat query N+1 pada halaman utama.

---

# 40. Urutan Implementasi untuk Agent

Kerjakan secara bertahap.

## Fase 1: Setup

1. Inisialisasi Laravel.
2. Instal starter kit Livewire.
3. Konfigurasi MySQL.
4. Konfigurasi timezone.
5. Konfigurasi bahasa.
6. Instal Spatie Laravel Permission.
7. Instal Laravel Excel.
8. Jalankan migration awal.

## Fase 2: Authentication dan Role

1. Konfigurasi login.
2. Nonaktifkan register publik jika diperlukan.
3. Buat role.
4. Buat permission.
5. Buat middleware akun aktif.
6. Buat admin seeder.

## Fase 3: Master Data

1. Buat modul divisi.
2. Buat modul pengguna.
3. Buat manajemen role pengguna.
4. Buat pencarian dan filter.

## Fase 4: Jadwal

1. Buat migration jadwal.
2. Buat model dan relasi.
3. Buat CRUD jadwal.
4. Buat penugasan pengguna.
5. Tambahkan validasi waktu.

## Fase 5: Absensi

1. Buat migration absensi.
2. Buat AttendanceService.
3. Buat check-in.
4. Buat check-out.
5. Buat status terlambat.
6. Buat validasi lokasi.
7. Buat riwayat absensi.

## Fase 6: Pengajuan

1. Buat migration pengajuan.
2. Buat form pengajuan.
3. Buat upload bukti.
4. Buat proses persetujuan.
5. Integrasikan dengan status absensi.

## Fase 7: Dashboard dan Laporan

1. Buat dashboard admin.
2. Buat dashboard pengguna.
3. Buat filter laporan.
4. Buat ringkasan statistik.
5. Buat export Excel.

## Fase 8: Otomatisasi

1. Buat command penandaan alfa.
2. Jadwalkan command.
3. Pastikan command idempotent.
4. Tambahkan audit log.

## Fase 9: Testing dan Finalisasi

1. Buat feature test.
2. Periksa authorization.
3. Periksa responsive layout.
4. Periksa validasi.
5. Periksa query N+1.
6. Jalankan formatter.
7. Jalankan seluruh test.
8. Perbarui README.

---

# 41. Definition of Done

Setiap fitur dianggap selesai jika:

* Migration tersedia.
* Model dan relasi tersedia.
* Authorization tersedia.
* Validasi tersedia.
* UI responsive.
* Loading dan empty state tersedia.
* Pesan sukses dan error tersedia.
* Test tersedia.
* Test berhasil.
* Tidak ada error console.
* Tidak ada data ganda.
* Tidak ada akses tanpa izin.
* Kode mengikuti Laravel convention.
* Dokumentasi singkat tersedia.

---

# 42. Perintah Dasar Agent

Gunakan perintah sesuai kebutuhan:

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan optimize:clear
php artisan route:list
php artisan test
npm install
npm run dev
npm run build
composer run dev
```

Sebelum menyelesaikan setiap fase, jalankan:

```bash
php artisan test
npm run build
```

---

# 43. Dokumentasi README

README harus berisi:

* Deskripsi aplikasi
* Persyaratan sistem
* Cara instalasi
* Cara membuat database
* Cara konfigurasi `.env`
* Cara menjalankan migration
* Cara menjalankan seeder
* Akun demo
* Cara menjalankan aplikasi
* Cara menjalankan test
* Struktur role
* Penjelasan fitur
* Screenshot opsional

---

# 44. Hasil Akhir yang Diharapkan

Agent harus menghasilkan aplikasi Laravel full-stack yang:

* Dapat dijalankan secara lokal.
* Memiliki autentikasi berbasis session.
* Memiliki role admin, operator, dan user.
* Memiliki dashboard yang berbeda berdasarkan role.
* Dapat melakukan check-in dan check-out.
* Dapat mendeteksi keterlambatan.
* Dapat memvalidasi lokasi.
* Dapat mengelola izin dan sakit.
* Dapat menghasilkan laporan.
* Aman dari akses tanpa izin.
* Responsive.
* Memiliki test untuk proses bisnis utama.
* Memiliki struktur kode yang mudah dikembangkan.
