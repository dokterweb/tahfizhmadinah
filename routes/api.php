<?php

use App\Http\Controllers\Api\AbsensiSiswaController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MadinaController;
use App\Http\Controllers\Api\SiswaDashboardController;
use App\Http\Controllers\Api\SiswaManzilController;
use App\Http\Controllers\Api\SiswaProfileController;
use App\Http\Controllers\Api\SiswaSabaqController;
use App\Http\Controllers\Api\SiswaSabqiController;
use App\Http\Controllers\Api\UstadzDashboardController;
use App\Http\Controllers\Api\UstadzManzilController;
use App\Http\Controllers\Api\UstadzProfileController;
use App\Http\Controllers\Api\UstadzSabaqController;
use App\Http\Controllers\Api\UstadzSabqiController;
use App\Http\Controllers\Api\UstadzSiswaController;
use App\Http\Controllers\Api\UstadzSiswaDetailController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/logout', [AuthController::class, 'logout']);
  
    Route::get('/ustadz/dashboard',[UstadzDashboardController::class, 'index']);

    Route::get('/ustadz/sub-kelas/{subKelas}/siswas',[UstadzSiswaController::class, 'index']);
    Route::get('/ustadz/siswa/{siswa}',[UstadzSiswaDetailController::class, 'show']);

    Route::get('/ustadz/profile', [UstadzProfileController::class, 'show']);
    Route::put('/ustadz/profile', [UstadzProfileController::class, 'update']);
    Route::put('/ustadz/profile/password', [UstadzProfileController::class, 'updatePassword']);
    Route::post('/ustadz/profile/avatar', [UstadzProfileController::class, 'updateAvatar']);

    Route::get('/ustadz/siswa/{siswa}/sabqi',[UstadzSabqiController::class, 'index']);
    Route::post('/ustadz/siswa/{siswa}/sabqi',[UstadzSabqiController::class, 'store']);
    Route::put('/ustadz/siswa/{siswa}/sabqi/{sabqi}',[UstadzSabqiController::class, 'update']);
    Route::delete('/ustadz/siswa/{siswa}/sabqi/{sabqi}',[UstadzSabqiController::class, 'destroy']);

    Route::get('/ustadz/siswa/{siswa}/manzil',[UstadzManzilController::class, 'index']);
    Route::post('/ustadz/siswa/{siswa}/manzil',[UstadzManzilController::class, 'store']);
    Route::put('/ustadz/siswa/{siswa}/manzil/{manzil}',[UstadzManzilController::class, 'update']);
    Route::delete('/ustadz/siswa/{siswa}/manzil/{manzil}',[UstadzManzilController::class, 'destroy']);

    Route::get('/ustadz/madina',[MadinaController::class, 'index']);
    
    Route::post('/ustadz/siswa/{siswa}/sabaq',[UstadzSabaqController::class, 'store']);
    Route::put('/ustadz/siswa/{siswa}/sabaq/{sabaq}',[UstadzSabaqController::class, 'update']);
    Route::delete('/ustadz/siswa/{siswa}/sabaq/{sabaq}',[UstadzSabaqController::class, 'destroy']);
    
    Route::get('/siswa/dashboard',[SiswaDashboardController::class, 'index']);
    Route::get('/siswa/sabaq',[SiswaSabaqController::class, 'index']);
    Route::get('/siswa/sabqi', [SiswaSabqiController::class, 'index']);
    Route::get('/siswa/manzil',[SiswaManzilController::class, 'index']);

    Route::get('/siswa/profile', [SiswaProfileController::class, 'show']);
    Route::put('/siswa/profile', [SiswaProfileController::class, 'update']);
    Route::put('/siswa/profile/password', [SiswaProfileController::class, 'updatePassword']);
    Route::post('/siswa/profile/avatar', [SiswaProfileController::class, 'updateAvatar']);
   
    Route::get('/ustadz/absensi',[AbsensiSiswaController::class, 'index']);
    Route::get('/ustadz/absensi/sub-kelas/{sub_kelas_id}/siswa',[AbsensiSiswaController::class, 'siswaBySubKelas']);    
    Route::post('/ustadz/absensi',[AbsensiSiswaController::class, 'store']);
    Route::get('/siswa/absensi',[AbsensiSiswaController::class, 'siswaAbsensi']);
    
});