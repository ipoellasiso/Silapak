<?php

use App\Http\Controllers\AnggaranController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BkuController;
use App\Http\Controllers\Bpkad\LaporanPajakKppController;
use App\Http\Controllers\Bpkad\VerifikasiTbpController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\KamarControlleruser;
use App\Http\Controllers\Landing_pageController;
use App\Http\Controllers\LaporanlsController;
use App\Http\Controllers\LaporanRealisasiController;
use App\Http\Controllers\LapRekaptppController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\Master\AkunPajakController;
use App\Http\Controllers\Opd\InputPajakController;
use App\Http\Controllers\OpdController;
use App\Http\Controllers\PajakTbpController;
use App\Http\Controllers\PdfUploadController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\Realisasi_hd_Controller;
use App\Http\Controllers\Realisasi_HD_Controller as ControllersRealisasi_HD_Controller;
use App\Http\Controllers\RealisasiController;
use App\Http\Controllers\RealisasiControllerAdmin;
use App\Http\Controllers\RekapantppController;
use App\Http\Controllers\RekeningController;
use App\Http\Controllers\ScanSp2dJsonController;
use App\Http\Controllers\SimpanSp2dsipdController;
use App\Http\Controllers\Sp2dController;
use App\Http\Controllers\Sp2dLogController;
use App\Http\Controllers\TarikSp2dController;
use App\Http\Controllers\UrusanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('Tampilan_tambahan.Landing_page');
// });

Route::get('/', [AuthController::class, 'login']);
// Route::get('/', [MaintenanceController::class, 'index']);

// AUTH
Route::get('/register', [AuthController::class, 'register']);
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/cek_login', [AuthController::class, 'cek_login']);
Route::get('/logout', [AuthController::class, 'logout']);

// HOME
Route::get('/home', [HomeController::class, 'index'])->middleware('auth:web','checkRole:Admin,User');

// DATA OPD
Route::get('/tampilopd', [OpdController::class, 'index'])->middleware('auth:web','checkRole:Admin,User');
Route::post('/opd/store', [OpdController::class, 'store'])->middleware('auth:web','checkRole:Admin');
Route::get('/opd/edit/{id}', [OpdController::class, 'edit'])->middleware('auth:web','checkRole:Admin');
Route::delete('/opd/destroy/{id}', [OpdController::class, 'destroy'])->middleware('auth:web','checkRole:Admin');

// DATA USER
Route::get('/tampiluser', [UserController::class, 'index'])->middleware('auth:web','checkRole:Admin');
Route::post('/user/store', [UserController::class, 'store'])->middleware('auth:web','checkRole:Admin');
Route::get('/user/edit/{id}', [UserController::class, 'edit'])->middleware('auth:web','checkRole:Admin');
Route::delete('/user/destroy/{id}', [UserController::class, 'destroy'])->middleware('auth:web','checkRole:Admin');
Route::post('/user/aktif/{id}', [UserController::class, 'aktif'])->middleware('auth:web','checkRole:Admin');
Route::post('/user/nonaktif/{id}', [UserController::class, 'nonaktif'])->middleware('auth:web','checkRole:Admin');
Route::get('/user/opd', [UserController::class, 'getDataopd'])->middleware('auth:web','checkRole:Admin');

Route::middleware(['auth'])->group(function () {

    Route::get('/pengajuan-tbp', [PajakTbpController::class, 'indextbp']);
    Route::get('/pengajuan-tbp/list', [PajakTbpController::class, 'indextbplist']);
    Route::get('/pengajuan-tbp/belum-verifikasi', [PajakTbpController::class, 'indextbpbelumverifikasi']);
    Route::get('/pengajuan-tbp/terima', [PajakTbpController::class, 'indextbpterima']);
    Route::get('/pengajuan-tbp/tolak', [PajakTbpController::class, 'indextbptolak']);

    Route::post('/simpanjsontbp', [PajakTbpController::class, 'save_jsontbp']);
    Route::delete('/pengajuan-tbp/{id}/hapus', [PajakTbpController::class, 'hapusTbp'])
    ->name('pengajuan-tbp.hapus');
    Route::get('/pengajuan-tbp/{id}/edit', [PajakTbpController::class, 'editTbp']);
    Route::put('/pengajuan-tbp/{id}', [PajakTbpController::class, 'updateTbp']);
    Route::get('/audit-log', [PajakTbpController::class, 'auditLog']);
    Route::get('/audit-log/data', [PajakTbpController::class, 'auditLogData']);

});

Route::middleware(['auth', 'checkRole:Admin'])->group(function () {

    Route::get('/bpkad/verifikasi-tbp', 
        [VerifikasiTbpController::class, 'index']);

    Route::get('/bpkad/verifikasi-tbp/verifikasi', 
        [VerifikasiTbpController::class, 'dataVerifikasi']);

    Route::get('/bpkad/verifikasi-tbp/terima', 
        [VerifikasiTbpController::class, 'dataTerima']);

    Route::get('/bpkad/verifikasi-tbp/tolak', 
        [VerifikasiTbpController::class, 'dataTolak']);

    Route::post('/bpkad/verifikasi-tbp/terima', [VerifikasiTbpController::class, 'terima'])->name('verifikasi-tbp.terima');

    Route::post('/bpkad/verifikasi-tbp/tolak', [VerifikasiTbpController::class, 'tolak'])->name('verifikasi-tbp.tolak');

    //     Route::post('/bpkad/verifikasi-tbp/tolak-dari-terima',
    // [VerifikasiTbpController::class, 'tolakDariTerima']);

});

//Verifikasi persatu
Route::post(
    '/bpkad/verifikasi-tbp/tolak-dari-terima',
    [VerifikasiTbpController::class, 'tolakDariTerima']
)->middleware(['auth','checkRole:Admin']);

//Verifikasi dipilih dan perhalaman
Route::prefix('bpkad')->middleware('auth')->group(function () {

    Route::get('/verifikasi-tbp', [VerifikasiTbpController::class, 'index']);

    // 🔥 DATA TABLE VERIFIKASI
    Route::get('/verifikasi-tbp/data', [VerifikasiTbpController::class, 'dataVerifikasi']);

    // 🔥 TERIMA MULTI (SELECT / PER HALAMAN)
    Route::post('/verifikasi-tbp/terima-multi', [VerifikasiTbpController::class, 'terimaMulti']);
});

//Input Pajak OPD
Route::middleware(['auth'])->group(function () {

    Route::get('/opd/input-pajak', 
        [InputPajakController::class, 'index']);

    Route::get('/opd/input-pajak/belum', 
        [InputPajakController::class, 'dataBelumInput']);

    Route::get('/opd/input-pajak/sudah', 
        [InputPajakController::class, 'dataSudahInput']);

    Route::post('/opd/input-pajak/simpan', 
        [InputPajakController::class, 'simpan']);

    Route::get('/opd/input-pajak/detail/{id}', 
    [InputPajakController::class, 'detail']);
});

// Akun Pajak
Route::prefix('master')->middleware('auth')->group(function () {
    Route::get('/akun-pajak', [AkunPajakController::class,'index']);
    Route::get('/akun-pajak/data', [AkunPajakController::class,'data']);
    Route::post('/akun-pajak/store', [AkunPajakController::class,'store']);
    Route::get('/akun-pajak/{id}', [AkunPajakController::class,'show']);
});

Route::get('/opd/input-pajak/{id}', function($id){
    return \App\Models\TbPotonganGu::findOrFail($id);
})->middleware('auth');

Route::post('/opd/input-pajak/batal', 
    [InputPajakController::class, 'batal']
)->name('opd.inputpajak.batal');

Route::middleware(['auth','checkRole:Admin'])->group(function () {
    Route::get('/bpkad/laporan-pajak-kpp', 
        [LaporanPajakKppController::class, 'index']
    );

    Route::get('/bpkad/laporan-pajak-kpp/data/sudah-sp2d', 
        [LaporanPajakKppController::class, 'dataSudahSp2d']
    )->name('laporan.kpp.sudah');

    Route::get('/bpkad/laporan-pajak-kpp/data/belum-sp2d', 
        [LaporanPajakKppController::class, 'dataBelumSp2d']
    )->name('laporan.kpp.belum');

     // 🔥 TAB BARU
    Route::get('/belum-posting', [LaporanPajakKppController::class, 'dataBelumPosting'])
        ->name('laporan.kpp.belumPosting');

});

Route::post('/bpkad/laporan-pajak-kpp/posting-massal',
    [LaporanPajakKppController::class,'postingMassal']
)->name('kpp.posting.massal');

Route::get(
    '/bpkad/laporan-pajak-kpp/export',
    [LaporanPajakKppController::class, 'export']
)->name('laporan.kpp.export');

