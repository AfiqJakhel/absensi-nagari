<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');

    // Admin Area
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        Route::get('/pegawai', function () {
            return view('admin.pegawai.index');
        })->name('pegawai');

        Route::get('/absensi', function () {
            return view('admin.absensi.index');
        })->name('absensi');

        Route::get('/riwayat', function () {
            return view('admin.riwayat.index');
        })->name('riwayat');

        Route::get('/akun', function () {
            return view('admin.akun.index');
        })->name('akun');
    });

    // Employee Area
    Route::get('/dashboard', function () {
        return view('pegawai.dashboard');
    })->name('dashboard');

    Route::get('/absensi', function () {
        return view('pegawai.absensi');
    })->name('absensi');

    Route::get('/riwayat', function () {
        return view('pegawai.riwayat');
    })->name('riwayat');
});
