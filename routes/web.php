<?php

use App\Http\Controllers\AbsensiSiswaController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IqroController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\ManzilController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\PayoutsiswaController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilesekolahController;
use App\Http\Controllers\SabaqController;
use App\Http\Controllers\SabqiController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SiswaDashboardController;
use App\Http\Controllers\SubKelasController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\UstadzController;
use App\Http\Controllers\UstadzDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Menampilkan halaman login
Route::get('/login', [LoginController::class, 'index'])
    ->name('login');

// Memproses login
Route::post('/login', [LoginController::class, 'handleLogin'])
    ->name('login.process');

// Logout
Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout');
    
Route::post('/telegram/webhook', [TelegramController::class, 'handleWebhook']);
Route::get('/set-webhook', [TelegramController::class, 'setWebhook']);

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('role:admin')->name('dashboard');
    Route::get('/ustadz/dashboard', [UstadzDashboardController::class, 'index'])->middleware('role:ustadz')->name('ustadz.dashboard');
    Route::get('/siswa/dashboard', [SiswaDashboardController::class, 'index'])->middleware('role:siswa')->name('siswa.dashboard');

    Route::get('settings/school', [ProfilesekolahController::class, 'edit'])->name('settings.school.edit');
    Route::post('settings/school', [ProfilesekolahController::class, 'update'])->name('settings.school.update');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/kelasnyas',[KelasController::class, 'index'])->middleware('role:admin')->name('kelasnyas');
    Route::post('kelasnyas/store', [KelasController::class, 'store'])->middleware('role:admin')->name('kelasnyas.store');
    Route::get('kelasnyas/{kelasnya}/edit', [KelasController::class, 'edit'])->middleware('role:admin')->name('kelasnyas.edit');
    Route::put('kelasnyas/{kelasnya}', [KelasController::class, 'update'])->middleware('role:admin')->name('kelasnyas.update');
    Route::delete('kelasnyas/{kelasnya}', [KelasController::class, 'destroy'])->middleware('role:admin')->name('kelasnyas.destroy');

    Route::prefix('sub-kelas')->middleware('role:admin')->group(function () {
        Route::get('/', [SubKelasController::class, 'index'])->name('sub_kelas.index');
        Route::post('/', [SubKelasController::class, 'store'])->name('sub_kelas.store');
        Route::get('/export-excel',[SubKelasController::class, 'exportExcel'])->name('sub-kelas.export.excel');
        Route::get('/{id}/edit', [SubKelasController::class, 'edit'])->name('sub_kelas.edit');
        Route::put('/{id}', [SubKelasController::class, 'update'])->name('sub_kelas.update');
        Route::delete('/{id}', [SubKelasController::class, 'destroy'])->name('sub_kelas.destroy');
    });

    Route::get('ustadzs/export-excel',[UstadzController::class, 'exportExcel'])->middleware('role:admin')->name('ustadzs.export.excel');
    Route::resource('ustadzs', UstadzController::class)->middleware('role:admin');

    Route::get('siswas/export-excel',[SiswaController::class, 'exportExcel'])->middleware('role:admin')->name('siswas.export.excel');
    Route::get('siswas/import',[SiswaController::class, 'importForm'])->middleware('role:admin')->name('siswas.import.form');
    Route::post('siswas/import/preview',[SiswaController::class, 'importPreview'])->middleware('role:admin')->name('siswas.import.preview');
    Route::post('siswas/import/store',[SiswaController::class, 'importStore'])->middleware('role:admin')->name('siswas.import.store');
    Route::resource('siswas', SiswaController::class)->middleware('role:admin');

    // Route::get('get-ustadz/{subKelas}', [SiswaController::class, 'getUstadzBySubKelas'])->middleware('role:admin');
    Route::get('/get-ustadz/{subKelas}', [SiswaController::class, 'getUstadz'])->middleware('role:admin')->name('get.ustadz');
    Route::get('sabaqs',[SabaqController::class, 'index'])->middleware('role:admin|ustadz')->name('sabaqs');
    Route::get('sabaq-history/{siswa_id}', [SabaqController::class, 'showSabaqHistory'])->middleware('role:admin|ustadz')->name('sabaq-history.show');
    // Route untuk mendapatkan data surat berdasarkan nomor surat (sura_no)
    Route::get('get-surat-details/{sura_no}', [SabaqController::class, 'getSuratDetails'])->middleware('role:admin|ustadz')->name('get.surat.details');
    Route::post('/sabaq/store', [SabaqController::class, 'store'])->middleware('role:admin|ustadz')->name('sabaq.store');
    Route::get('sabaq-history/{siswa_id}/edit/{id}', [SabaqController::class, 'edit'])->middleware('role:admin')->name('sabaq-history.edit');
    Route::post('sabaq-history/{siswa_id}/update/{id}', [SabaqController::class, 'update'])->middleware('role:admin')->name('sabaq-history.update');
    Route::delete('sabaq-history/{siswa_id}/{id}', [SabaqController::class, 'destroy'])->middleware('role:admin|ustadz')->name('sabaq-history.destroy');

    Route::get('sabaqs/sabaqsiswa', [SabaqController::class, 'showSiswaHistory'])->middleware('role:siswa')->name('sabaqs.sabaqsiswa');
    Route::get('sabaq/history/{id}/edit', [SabaqController::class, 'getHistory'])->middleware('role:admin|ustadz')->name('sabaq.history.edit');
    Route::put('/sabaq/history/{id}/update', [SabaqController::class, 'update'])->name('sabaq.history.update');
    Route::get('sabaqs/laporan', [SabaqController::class, 'laporan'])->middleware('role:admin|ustadz')->name('sabaqs.laporan');
    Route::get('sabaqs/export', [SabaqController::class, 'exportToExcel'])->middleware('role:admin|ustadz')->name('sabaqs.export');

    Route::get('sabqis',[SabqiController::class, 'index'])->middleware('role:admin|ustadz')->name('sabqis');
    Route::get('sabqi-history/{siswa_id}', [SabqiController::class, 'showSabqiHistory'])->middleware('role:admin|ustadz')->name('sabqi-history.show');
    Route::get('get-surat-details/{sura_no}', [SabqiController::class, 'getSuratDetails'])->middleware('role:admin|ustadz')->name('get.surat.detailsforsabqi');
    
    Route::post('/sabqi/store', [SabqiController::class, 'store'])->middleware('role:admin|ustadz')->name('sabqi.store');
    Route::get('sabqis/sabqisiswa', [SabqiController::class, 'showSiswaHistory'])->middleware('role:siswa')->name('sabqis.sabqisiswa');
    Route::get('sabqi-history/{siswa_id}/edit/{id}', [SabqiController::class, 'edit'])->middleware('role:admin')->name('sabqi-history.edit');
    
    Route::put('/sabqi/history/{id}/update', [SabqiController::class, 'update'])->name('sabqis.history.update');
    Route::delete('sabqi-history/{siswa_id}/{id}', [SabqiController::class, 'destroy'])->middleware('role:admin|ustadz')->name('sabqi-history.destroy');
    Route::get('sabqi/history/{id}/edit', [SabqiController::class, 'getHistory'])->middleware('role:admin|ustadz')->name('sabqi.history.edit');
    Route::get('sabqis/laporan', [SabqiController::class, 'laporan'])->middleware('role:admin|ustadz')->name('sabqis.laporan');
    Route::get('sabqis/export', [SabqiController::class, 'exportToExcel'])->middleware('role:admin|ustadz')->name('sabqis.export');

    Route::get('manzils',[ManzilController::class, 'index'])->middleware('role:admin|ustadz')->name('manzils');
    Route::get('manzil-history/{siswa_id}', [ManzilController::class, 'showmanzilHistory'])->middleware('role:admin|ustadz')->name('manzil-history.show');
    Route::post('/manzil/store', [ManzilController::class, 'store'])->middleware('role:admin|ustadz')->name('manzil.store');
    Route::delete('manzil-history/{siswa_id}/{id}', [ManzilController::class, 'destroy'])->middleware('role:admin')->name('manzil-history.destroy');
    Route::get('manzil/history/{id}/edit', [ManzilController::class, 'getHistory'])->middleware('role:admin|ustadz')->name('manzil.history.edit');
    Route::put('/manzil/history/{id}/update', [ManzilController::class, 'update'])->name('manzils.history.update');

    Route::get('manzils/manzilsiswa', [ManzilController::class, 'showSiswaHistory'])->middleware('role:siswa')->name('manzils.manzilsiswa');
    Route::get('get-surat-manzil/{sura_no}', [ManzilController::class, 'getSuratmanzil'])->middleware('role:admin|ustadz')->name('get.surat.details');
    Route::get('manzils/history/{id}/edit', [ManzilController::class, 'edit'])->name('manzils.history.edit');
    Route::put('manzils/history/{id}/update', [ManzilController::class, 'update'])->name('manzils.history.update');
    Route::get('manzils/laporan', [ManzilController::class, 'laporan'])->middleware('role:admin|ustadz')->name('manzils.laporan');
    Route::get('manzils/export', [ManzilController::class, 'exportToExcel'])->middleware('role:admin|ustadz')->name('manzils.export');

    Route::get('iqros',[IqroController::class, 'index'])->middleware('role:admin|ustadz')->name('iqros');
    Route::get('iqro-history/{siswa_id}', [IqroController::class, 'showiqroHistory'])->middleware('role:admin|ustadz')->name('iqro-history.show');
    // Route untuk store iqro history
    Route::post('iqro-history/{siswa_id}/store', [IqroController::class, 'store'])->middleware('role:admin|ustadz')->name('iqro-history.store');
    Route::get('iqro-history/{siswa_id}/{id}/edit', [IqroController::class, 'edit'])->middleware('role:admin')->name('iqro-history.edit');
    Route::put('iqro-history/{siswa_id}/{id}/update', [IqroController::class, 'update'])->middleware('role:admin')->name('iqro-history.update');
    Route::get('iqros/iqrosiswa', [IqroController::class, 'showSiswaHistory'])->middleware('role:siswa')->name('iqros.iqrosiswa');
    Route::delete('iqro-history/{id}', [IqroController::class, 'destroy'])->middleware('role:admin')->name('iqro-history.destroy');
    Route::get('iqros/laporan', [IqroController::class, 'laporan'])->middleware('role:admin|ustadz')->name('iqros.laporan');
    Route::get('iqros/export', [IqroController::class, 'exportToExcel'])->middleware('role:admin|ustadz')->name('iqros.export');

    Route::get('absensis', [AbsensiSiswaController::class, 'index'])->middleware('role:admin|ustadz')->name('absensis');
    Route::get('absensis/create', [AbsensiSiswaController::class, 'create'])->middleware('role:admin|ustadz')->name('absensis.create');
    Route::post('absensis/store', [AbsensiSiswaController::class, 'store'])->middleware('role:admin|ustadz')->name('absensis.store');
    Route::get('/get-siswa/{sub_kelas_id}', [AbsensiSiswaController::class, 'getSiswaBySubKelas'])->middleware('role:admin|ustadz')->name('get-siswa.show');
    Route::post('absensis/check-absensi', [AbsensiSiswaController::class, 'checkAbsensi'])->middleware('role:admin|ustadz')->name('check-absensi');
    Route::delete('absensis/{id}', [AbsensiSiswaController::class, 'destroy'])->middleware('role:admin|ustadz')->name('absensis.destroy');
    Route::get('absensis/ustadzIndex', [AbsensiSiswaController::class, 'ustadzIndex'])->middleware('role:admin|ustadz')->name('absensis.ustadzIndex');
    Route::get('absensis/ustadz/create', [AbsensiSiswaController::class, 'ustadzCreate'])->name('absensis.ustadzCreate');
    Route::post('absensis/ustadz/store', [AbsensiSiswaController::class, 'ustadzStore'])->name('absensis.ustadzStore');
    
    Route::get('periodes',[PeriodeController::class, 'index'])->middleware('role:admin')->name('periodes');
    Route::post('periodes/store', [PeriodeController::class, 'store'])->middleware('role:admin')->name('periodes.store');
    Route::get('periodes/{periode}/edit', [PeriodeController::class, 'edit'])->middleware('role:admin')->name('periodes.edit');
    Route::put('periodes/{periode}', [PeriodeController::class, 'update'])->middleware('role:admin')->name('periodes.update');
    Route::delete('periodes/{periode}', [PeriodeController::class, 'destroy'])->middleware('role:admin')->name('periodes.destroy');

    Route::get('posnyas',[PosController::class, 'index'])->middleware('role:admin')->name('posnyas');
    Route::post('posnyas/store', [PosController::class, 'store'])->middleware('role:admin')->name('posnyas.store');
    Route::get('posnyas/{posnya}/edit', [PosController::class, 'edit'])->middleware('role:admin')->name('posnyas.edit');
    Route::put('posnyas/{posnya}', [PosController::class, 'update'])->middleware('role:admin')->name('posnyas.update');
    Route::delete('posnyas/{posnya}', [PosController::class, 'destroy'])->middleware('role:admin')->name('posnyas.destroy');

    Route::get('payments',[PaymentController::class, 'index'])->middleware('role:admin')->name('payments');
    Route::get('payments/create', [PaymentController::class, 'create'])->middleware('role:admin')->name('payments.create');
    Route::post('payments/store', [PaymentController::class, 'store'])->middleware('role:admin')->name('payments.store');
    Route::get('payments/{payment}/edit', [PaymentController::class, 'edit'])->middleware('role:admin')->name('payments.edit');
    Route::put('payments/{payment}', [PaymentController::class, 'update'])->middleware('role:admin')->name('payments.update');
    Route::get('payments/view_bulan/{payment}', [PaymentController::class, 'view_bulan'])->middleware('role:admin')->name('payments.view_bulan');
    Route::get('payments/view_bulan/{payment}/filter', [PaymentController::class, 'filterBulanans'])
     ->middleware('role:admin')
     ->name('payments.filter_bulanans');
     Route::get('payments/add_payment_bulan/{payment}', [PaymentController::class, 'add_payment_bulan'])
    ->middleware('role:admin')->name('payments.add_payment_bulan');
    Route::post('payments/store_bulanans/{payment}', [PaymentController::class, 'storeBulanans'])
    ->middleware('role:admin')->name('payments.storeBulanans');

    Route::get('payouts', [PayoutController::class, 'index'])->middleware('role:admin')->name('payouts.index');
    Route::get('payouts/filter_bulanans', [PayoutController::class, 'filter_bulanans'])->middleware('role:admin')->name('payouts.filter_bulanans');
    Route::get('payouts/bayar_bulan/{payment}/{siswa}', [PayoutController::class, 'bayar_bulan'])->middleware('role:admin')->name('payouts.bayar_bulan');
    Route::put('payouts/updatebulanans', [PayoutController::class, 'updateBulanans'])->name('payouts.updatebulanans');
    Route::get('payouts/invoice/{payment}/{bulan}', [PayoutController::class, 'invoice'])
    ->middleware('role:admin')
    ->name('payouts.invoice');


    Route::get('payoutsiswas', [PayoutsiswaController::class, 'index'])->middleware('role:siswa')->name('payoutsiswas.index');
    Route::get('payoutsiswas/filter_bulanans', [PayoutsiswaController::class, 'filter_bulanans'])
    ->middleware('role:siswa')->name('payoutsiswas.filter_bulanans');
    Route::get('payoutsiswas/tagihan_bulan/{payment}/{siswa}', [PayoutsiswaController::class, 'tagihan_bulan'])
    ->middleware('role:siswa')->name('payoutsiswas.tagihan_bulan');
    // untuk mulai bayar
    Route::put('payoutsiswas/bayarbulanan', [PayoutsiswaController::class, 'bayarbulanan'])
    ->name('payoutsiswas.bayarbulanan');

    // untuk update status setelah sukses
    Route::post('payoutsiswas/update-status', [PayoutsiswaController::class, 'updateStatus'])
    ->name('payoutsiswas.update_status');

});
