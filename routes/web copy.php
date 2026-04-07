<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PermohonanController;
use App\Http\Controllers\KomentarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KegiatanFolderController;
use App\Http\Controllers\KegiatanFileController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\SambutanController;
use App\Http\Controllers\PostSocialMediaController;
use Inertia\Inertia;



Route::get('/', [LandingPageController::class, 'index'])->name('landing.index');

Route::get('/kegiatan/{slug}', [LandingPageController::class, 'showPublic'])->name('kegiatanpublic.folders.show');
Route::get('/kegiatan/{slug}/download-zip', [LandingPageController::class, 'downloadZip'])->name('kegiatan.download.zip');
// Route::get('/kegiatan/file/{id}/download', [LandingPageController::class, 'downloadFile'])->name('public.file.download');
Route::post('/kegiatan/file/{id}/download', [LandingPageController::class, 'incrementDownload']);
Route::post('/feedback', [FeedbackController::class, 'store']);

Route::get('api/permohonan/terbaru', fn() =>
    \App\Models\Permohonan::with('user')->latest()->take(5)->get()
);

Route::get('api/kegiatan/folder-publik', fn() =>
    \App\Models\KegiatanFolder::with(['files' => fn($q) => $q->where('checked', 1)])
        ->where('is_public', 1)
        ->latest()->take(5)->get()
);


Route::post('/register', [RegisterController::class, 'store'])->name('register');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/permohonan', [PermohonanController::class, 'index'])->name('permohonan.index');
    Route::get('/permohonan/create', [PermohonanController::class, 'create'])->name('permohonan.create');
    Route::post('/permohonan', [PermohonanController::class, 'store'])->name('permohonan.store');
    Route::get('/permohonan/{id}', [PermohonanController::class, 'show'])->name('permohonan.show');
    Route::get('/permohonan/{id}/edit', [PermohonanController::class, 'edit'])->name('permohonan.edit');
    Route::put('/permohonan/{id}', [PermohonanController::class, 'update'])->name('permohonan.update');
    Route::post('/permohonan/{id}/komentar', [PermohonanController::class, 'storeKomentar']);
    Route::delete('/komentar/{id}', [KomentarController::class, 'destroy'])->name('komentar.destroy');
    Route::get('/permohonan/{id}/komentar-terbaru', [PermohonanController::class, 'komentarTerbaru']);
    Route::put('/permohonan/{id}/update-status', [PermohonanController::class, 'updateStatus']);
    Route::delete('/permohonan/{id}', [PermohonanController::class, 'destroy'])->name('permohonan.destroy');

    Route::resource('berita', BeritaController::class);
    Route::delete('/berita/{id}', [BeritaController::class, 'destroy'])->name('berita.destroy');
    Route::put('/berita/tampilkan/{id}', [BeritaController::class, 'tampilkan'])->name('berita.tampilkan');
    Route::put('/berita/{id}/sembunyikan', [BeritaController::class, 'sembunyikan'])->name('berita.sembunyikan');

    Route::resource('sambutan', SambutanController::class);
    Route::resource('post-sosmed', PostSocialMediaController::class);

    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/kegiatan', [KegiatanFolderController::class, 'index'])->name('kegiatan.folders.index');
    Route::post('/kegiatan', [KegiatanFolderController::class, 'store'])->name('kegiatan.folders.store');
    Route::patch('/kegiatan/folders/{id}/toggle-favorite', [KegiatanFolderController::class, 'toggleFavorite'])->name('kegiatan.folders.toggle-favorite');

    Route::put('/kegiatan/folders/{id}', [KegiatanFolderController::class, 'update'])->name('kegiatan.folders.update');
    Route::delete('/kegiatan/folders/{id}', [KegiatanFolderController::class, 'destroy'])->name('kegiatan.folders.destroy');
    Route::get('/kegiatan/folders/{slug}/shared', [KegiatanFolderController::class, 'sharedView'])->name('kegiatan.folders.share.link');
    // Route::put('/kegiatan/folders/{folder}/share', [KegiatanFolderController::class, 'updateShareStatus'])->name('kegiatan.folders.share');

    Route::patch('/kegiatan/folders/{id}/toggle-public', [KegiatanFolderController::class, 'togglePublic'])->name('kegiatan.folders.togglePublic');
    Route::patch('/kegiatan/files/{id}/toggle-checked', [KegiatanFileController::class, 'toggleChecked'])->name('kegiatan.files.toggleChecked');
    Route::delete('/kegiatan/files/{file}', [KegiatanFileController::class, 'destroy'])->name('kegiatan.files.destroy');

    Route::get('/kegiatan/folders/pindah', [KegiatanFileController::class, 'indexmovecopy']);
    Route::post('/kegiatan/files/{file}/move', [KegiatanFileController::class, 'move']);
    Route::post('/kegiatan/files/{file}/copy', [KegiatanFileController::class, 'copy']);

    Route::get('/kegiatan/load-more', [KegiatanFolderController::class, 'loadMore'])->name('kegiatan.folders.loadMore');

    Route::get('/kegiatan/folders/{slug}', [KegiatanFolderController::class, 'show'])->name('kegiatan.folders.show');
    Route::post('/kegiatan/folders/{slug}', [KegiatanFolderController::class, 'store'])->name('kegiatansub.folders.store');

    // Route::post('/kegiatan/folders', [App\Http\Controllers\KegiatanFolderController::class, 'store'])->name('kegiatan.folders.store');
    
    Route::get('/kegiatan/{folder}/files', [KegiatanFileController::class, 'index']);

   
    Route::post('/kegiatan/files/{slug}', [App\Http\Controllers\KegiatanFileController::class, 'store'])->name('kegiatan.files.store');

    Route::get('/kegiatan/{folder}/pengaturan', [KegiatanFolderController::class, 'settings'])->name('kegiatan.folders.settings');
    // Route::put('/kegiatan/{folder}', [KegiatanFolderController::class, 'update'])->name('kegiatan.folders.update');


});


// Route::get('/cek-role', function () {
//     return response()->json([
//         'user' => auth()->user(),
//         'role' => auth()->user()?->role,
//     ]);
// })->middleware(['auth']);