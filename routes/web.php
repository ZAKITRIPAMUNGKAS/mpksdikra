<?php

use App\Http\Controllers\Admin\AbsensiController;
use App\Http\Controllers\Admin\AfektifController;
use App\Http\Controllers\Admin\AnggotaController as AdminAnggotaController;
use App\Http\Controllers\Admin\AnggotaImportController;
use App\Http\Controllers\Admin\AngketController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventOutputController;
use App\Http\Controllers\Admin\ExecutiveController;
use App\Http\Controllers\Admin\FasilitatorController;
use App\Http\Controllers\Admin\FormPertanyaanController;
use App\Http\Controllers\Admin\KeaktifanController;
use App\Http\Controllers\Admin\OutboundKelompokController;
use App\Http\Controllers\Admin\PesertaController;
use App\Http\Controllers\Admin\PsikomotorController;
use App\Http\Controllers\Admin\RtlAdminController;
use App\Http\Controllers\Admin\SesiController;
use App\Http\Controllers\Admin\SoalController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnggotaRegistrationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CekStatusController;
use App\Http\Controllers\FotoController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Portal\AfektifController as PortalAfektifController;
use App\Http\Controllers\Portal\AngketController as PortalAngketController;
use App\Http\Controllers\Portal\DashboardController as PortalDashboardController;
use App\Http\Controllers\Portal\KehadiranController;
use App\Http\Controllers\Portal\ProfileController as PortalProfileController;
use App\Http\Controllers\Portal\RtlController as PortalRtlController;
use App\Http\Controllers\Portal\SertifikatController;
use App\Http\Controllers\Portal\TesController as PortalTesController;
use App\Http\Controllers\SystemHelpController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/cek-status', [CekStatusController::class, 'index'])->name('cek-status');
Route::post('/cek-status', [CekStatusController::class, 'check'])->middleware('throttle:5,1')->name('cek-status.check');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
    Route::get('/daftar', [AnggotaRegistrationController::class, 'show'])->name('anggota.register');
    Route::get('/pendaftaran', fn () => redirect()->route('anggota.register'));
    Route::post('/daftar', [AnggotaRegistrationController::class, 'store'])->name('anggota.register.store');
});

Route::post('/logout', LogoutController::class)->middleware('auth')->name('logout');

Route::get('/foto/anggota/{anggota}', [FotoController::class, 'show'])
    ->middleware('auth')
    ->name('foto.anggota');

Route::middleware('auth')->group(function () {
    Route::get('/panduan', [SystemHelpController::class, 'panduan'])->name('panduan');
    Route::get('/updates', [SystemHelpController::class, 'updates'])->name('updates');
    Route::get('/sesi/{sesi}/materi', [SesiController::class, 'downloadMateri'])->name('sesi.materi.download');
});

Route::middleware(['auth', 'role:admin,admin_cabang,fasilitator,executive'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Executive: read-only overview
    Route::middleware('role:executive')->group(function () {
        Route::get('/executive', [ExecutiveController::class, 'index'])->name('executive');
    });

    // Events create & store: Admin Daerah only (sebelum route model binding {event})
    Route::middleware('role:admin')->group(function () {
        Route::get('/events/create', [EventController::class, 'create'])->name('events.create');
        Route::post('/events', [EventController::class, 'store'])->name('events.store');
    });

    // Read event list/detail/reports: admin daerah, facilitator, executive (event_access scopes per event)
    Route::middleware(['role:admin,fasilitator,executive'])->group(function () {
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/events/{event}', [EventController::class, 'show'])->middleware('event_access')->name('events.show');
        Route::get('/events/{event}/ranking', [EventOutputController::class, 'ranking'])->middleware('event_access')->name('events.ranking');
        Route::get('/events/{event}/report', [EventOutputController::class, 'report'])->middleware('event_access')->name('events.report');
        Route::get('/events/{event}/export-excel', [EventOutputController::class, 'exportExcel'])->middleware('event_access')->name('events.exportExcel');
        Route::get('/events/{event}/id-cards-batch', [EventOutputController::class, 'idCardsBatch'])->middleware('event_access')->name('events.idcards.batch');
        Route::get('/events/{event}/certificates-batch', [EventOutputController::class, 'certificatesBatch'])->middleware('event_access')->name('events.certificates.batch');
        Route::get('/events/{event}/outbound', [OutboundKelompokController::class, 'index'])->middleware('event_access')->name('events.outbound.index');
        Route::get('/events/{event}/outbound-pdf', [OutboundKelompokController::class, 'exportPdf'])->middleware('event_access')->name('events.outbound.pdf');
    });

    // Admin Daerah only: anggota import
    Route::middleware('role:admin')->group(function () {
        Route::get('/anggota/import', [AnggotaImportController::class, 'show'])->name('anggota.import');
        Route::get('/anggota/template-excel', [AnggotaImportController::class, 'downloadTemplate'])->name('anggota.import.template');
        Route::post('/anggota/import', [AnggotaImportController::class, 'store'])->name('anggota.import.store');
    });

    // Anggota management: Admin Daerah & Admin Cabang
    Route::middleware('role:admin,admin_cabang')->group(function () {
        Route::get('/anggota', [AdminAnggotaController::class, 'index'])->name('anggota.index');
        Route::get('/anggota/export', [AdminAnggotaController::class, 'export'])->name('anggota.export');
        Route::get('/anggota/{anggota}', [AdminAnggotaController::class, 'show'])->name('anggota.show');
        Route::get('/anggota/{anggota}/cetak-pdf', [AdminAnggotaController::class, 'cetakPdf'])->name('anggota.cetak-pdf');
        Route::post('/anggota/{anggota}/verify', [AdminAnggotaController::class, 'verify'])->name('anggota.verify');
        Route::post('/anggota/{anggota}/reject', [AdminAnggotaController::class, 'reject'])->name('anggota.reject');
        Route::post('/anggota/{anggota}/deactivate', [AdminAnggotaController::class, 'deactivate'])->name('anggota.deactivate');
        Route::post('/anggota/{anggota}/activate', [AdminAnggotaController::class, 'activate'])->name('anggota.activate');
        Route::post('/anggota/{anggota}/reset-password', [AdminAnggotaController::class, 'resetPassword'])->name('anggota.reset-password');
        Route::delete('/anggota/{anggota}', [AdminAnggotaController::class, 'destroy'])->name('anggota.destroy');
    });

    // Admin Daerah only: users + event config
    Route::middleware('role:admin')->group(function () {

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle', [UserController::class, 'toggleStatus'])->name('users.toggle');

        // Admin Daerah: Google-Form-style Form Kader Builder
        Route::get('/form-kader', [FormPertanyaanController::class, 'index'])->name('form-kader.index');
        Route::post('/form-kader', [FormPertanyaanController::class, 'store'])->name('form-kader.store');
        Route::put('/form-kader/{pertanyaan}', [FormPertanyaanController::class, 'update'])->name('form-kader.update');
        Route::delete('/form-kader/{pertanyaan}', [FormPertanyaanController::class, 'destroy'])->name('form-kader.destroy');
        Route::post('/form-kader/{pertanyaan}/toggle-aktif', [FormPertanyaanController::class, 'toggleAktif'])->name('form-kader.toggle-aktif');
        Route::post('/form-kader/{pertanyaan}/toggle-wajib', [FormPertanyaanController::class, 'toggleWajib'])->name('form-kader.toggle-wajib');
        Route::post('/form-kader/{pertanyaan}/reorder', [FormPertanyaanController::class, 'reorder'])->name('form-kader.reorder');

        Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
        Route::delete('/events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
        Route::post('/events/{event}/status', [EventController::class, 'updateStatus'])->name('events.status');
        Route::post('/events/{event}/fasilitator', [FasilitatorController::class, 'update'])->name('events.fasilitator.update');
        Route::post('/events/{event}/outbound/generate', [OutboundKelompokController::class, 'generate'])->name('events.outbound.generate');
        Route::post('/events/{event}/outbound/reset', [OutboundKelompokController::class, 'reset'])->name('events.outbound.reset');

        Route::get('/events/{event}/enroll', [PesertaController::class, 'search'])->name('events.enroll');
        Route::post('/events/{event}/enroll', [PesertaController::class, 'enroll'])->name('events.enroll.store');
        Route::post('/events/{event}/peserta/{peserta}/remove', [PesertaController::class, 'remove'])->name('events.peserta.remove');

        Route::post('/events/{event}/sesi', [SesiController::class, 'store'])->name('events.sesi.store');
        Route::put('/events/{event}/sesi/{sesi}', [SesiController::class, 'update'])->name('events.sesi.update');
        Route::delete('/events/{event}/sesi/{sesi}', [SesiController::class, 'destroy'])->name('events.sesi.destroy');

        Route::post('/events/{event}/soal/{tipe}', [SoalController::class, 'store'])->name('events.soal.store');
        Route::post('/events/{event}/soal/{tipe}/clone', [SoalController::class, 'cloneFromEvent'])->name('events.soal.clone');
        Route::delete('/events/{event}/soal/{soal}', [SoalController::class, 'destroy'])->name('events.soal.destroy');
        Route::post('/events/{event}/soal/{tipe}/open', [SoalController::class, 'openSession'])->name('events.soal.open');
        Route::post('/events/{event}/soal/{tipe}/close', [SoalController::class, 'closeSession'])->name('events.soal.close');

        Route::post('/events/{event}/psikomotor/template', [PsikomotorController::class, 'storeTemplate'])->name('events.psikomotor.template');
        Route::delete('/events/{event}/psikomotor/template/{template}', [PsikomotorController::class, 'destroyTemplate'])->name('events.psikomotor.template.destroy');

        Route::get('/events/{event}/afektif', [AfektifController::class, 'index'])->name('events.afektif');
        Route::post('/events/{event}/afektif/sub-aspek', [AfektifController::class, 'storeSubAspek'])->name('events.afektif.sub.store');
        Route::post('/events/{event}/afektif/sub-aspek/{subAspek}/butir', [AfektifController::class, 'storeButir'])->name('events.afektif.butir.store');
        Route::delete('/events/{event}/afektif/butir/{butir}', [AfektifController::class, 'destroyButir'])->name('events.afektif.butir.destroy');

        Route::get('/events/{event}/angket', [AngketController::class, 'index'])->name('events.angket');
        Route::post('/events/{event}/angket/item', [AngketController::class, 'store'])->name('events.angket.item.store');
        Route::delete('/events/{event}/angket/item/{item}', [AngketController::class, 'destroy'])->name('events.angket.item.destroy');

        Route::get('/events/{event}/rtl', [RtlAdminController::class, 'index'])->name('events.rtl.index');
        Route::get('/events/{event}/rtl/{rtl}', [RtlAdminController::class, 'show'])->name('events.rtl.show');
        Route::post('/events/{event}/rtl/{rtl}/review', [RtlAdminController::class, 'review'])->name('events.rtl.review');
        Route::post('/events/{event}/rtl-soal', [RtlAdminController::class, 'storeSoal'])->name('events.rtl.soal.store');
        Route::delete('/events/{event}/rtl-soal/{soal}', [RtlAdminController::class, 'destroySoal'])->name('events.rtl.soal.destroy');
        Route::get('/events/{event}/rtl/{rtl}/bukti/{jawabanId}', [RtlAdminController::class, 'downloadBukti'])->name('events.rtl.bukti');

        Route::get('/events/{event}/peserta/{peserta}/certificate', [EventOutputController::class, 'certificate'])->name('events.certificate');
    });

    // Operational routes: admin + assigned facilitator (event_access)
    Route::middleware(['role:admin,fasilitator', 'event_access'])->group(function () {
        Route::get('/events/{event}/soal/{tipe}', [SoalController::class, 'index'])->name('events.soal.index');
        Route::get('/events/{event}/psikomotor', [PsikomotorController::class, 'index'])->name('events.psikomotor');
        Route::get('/events/{event}/psikomotor/data', [PsikomotorController::class, 'data'])->name('events.psikomotor.data');
        Route::post('/events/{event}/psikomotor/save', [PsikomotorController::class, 'saveRow'])->name('events.psikomotor.save');
        Route::get('/events/{event}/keaktifan', [KeaktifanController::class, 'index'])->name('events.keaktifan');
        Route::post('/events/{event}/keaktifan', [KeaktifanController::class, 'store'])->name('events.keaktifan.store');
        Route::delete('/events/{event}/keaktifan/{keaktifan}', [KeaktifanController::class, 'destroy'])->name('events.keaktifan.destroy');
        Route::get('/events/{event}/sesi/{sesi}/scan', [AbsensiController::class, 'scanPage'])->name('events.absensi.scan');
        Route::get('/events/{event}/sesi/{sesi}/recent', [AbsensiController::class, 'recent'])->name('events.absensi.recent');
        Route::get('/events/{event}/sesi/{sesi}/cetak-lembar', [AbsensiController::class, 'cetakLembar'])->name('events.absensi.cetak');
        Route::post('/events/{event}/absensi/process', [AbsensiController::class, 'process'])->name('events.absensi.process');
        Route::post('/events/{event}/absensi/manual', [AbsensiController::class, 'manual'])->name('events.absensi.manual');
    });
});

Route::middleware(['auth', 'role:anggota'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/dashboard', [PortalDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [PortalProfileController::class, 'index'])->name('profile.index');
    Route::put('/profil', [PortalProfileController::class, 'update'])->name('profile.update');
    Route::get('/biodata-pdf', [PortalProfileController::class, 'cetakPdf'])->name('profile.cetak-pdf');

    Route::get('/tes', [PortalTesController::class, 'index'])->name('tes.index');
    Route::get('/tes/{event}/{tipe}', [PortalTesController::class, 'show'])->name('tes.show');
    Route::post('/tes/{event}/{tipe}', [PortalTesController::class, 'submit'])->name('tes.submit');

    Route::get('/afektif/{event}', [PortalAfektifController::class, 'index'])->name('afektif.index');
    Route::post('/afektif/{event}', [PortalAfektifController::class, 'save'])->name('afektif.save');

    Route::get('/angket/{event}', [PortalAngketController::class, 'index'])->name('angket.index');
    Route::post('/angket/{event}', [PortalAngketController::class, 'save'])->name('angket.save');

    Route::get('/rtl/{event}', [PortalRtlController::class, 'index'])->name('rtl.index');
    Route::post('/rtl/{event}', [PortalRtlController::class, 'submit'])->name('rtl.submit');

    Route::get('/kehadiran/{event}', [KehadiranController::class, 'index'])->name('kehadiran.index');
    Route::get('/id-card/{event}', [KehadiranController::class, 'idCard'])->name('idcard.download');
    Route::get('/sertifikat/{event}', [SertifikatController::class, 'download'])->name('sertifikat.download');
});
