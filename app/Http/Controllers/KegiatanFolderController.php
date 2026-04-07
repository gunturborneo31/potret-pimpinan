<?php

namespace App\Http\Controllers;

use App\Models\KegiatanFolder;
use App\Models\KegiatanFile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class KegiatanFolderController extends Controller
{

public function settings(KegiatanFolder $folder)
    {
        $this->authorize('update', $folder); // optional jika pakai policy

        return inertia('Kegiatan/FolderSettings', [
            'folder' => $folder
        ]);
    }

public function update(Request $request, $id)
{
    $request->validate([
        'judul' => 'required|string|max:255',
        'tanggal_kegiatan' => 'required|date',
        'pejabat_hadir' => 'required|in:Gubernur,Wakil Gubernur,Sekretaris Daerah',
    ]);

    $folder = KegiatanFolder::findOrFail($id);

    // Optional: auth check jika hanya pemilik folder boleh rename
    // if (auth()->id() !== $folder->user_id) {
    //     abort(403, 'Tidak diizinkan.');
    // }

    $folder->judul = $request->judul;
    $folder->slug = \Str::slug($request->judul) . '-' . \Str::random(6); // update slug
    $folder->tanggal_kegiatan = $request->tanggal_kegiatan;
    $folder->pejabat_hadir = $request->pejabat_hadir;
    $folder->save();

    return back()->with('success', 'Folder berhasil diubah.');
}

public function destroy($id)
{
     $folder = KegiatanFolder::findOrFail($id);

    // Hapus semua file terkait (jika ada relasi)
    $folder->files()->delete();

    $folder->delete();

    return back()->with('success', 'Folder berhasil dihapus.');

}



public function show($slug)
{
    $folder = KegiatanFolder::with('parent.parent.parent') // recursive sampai cukup
    ->where('slug', $slug)
    ->firstOrFail();
    $subfolders = $folder->children()->get();

  $files = KegiatanFile::with(['folder:id,user_id', 'folder.user:id,name'])
    ->where('kegiatan_folder_id', $folder->id)
    ->latest()
    ->paginate(500);

        // return $files;

    return Inertia::render('Kegiatan/Show', [
        'folder' => $folder,
        'is_secret' => $folder->is_secret,   // pastikan dikirim
        'slug_secret' => $folder->slug_secret, // juga dikirim
        'subfolders' => $subfolders,
        'files' => fn () => $files, // gunakan fn agar paginasi tidak eager-load duluan
         'auth' => [
        'user' => auth()->user(),
    ]
    ]);
}

    public function showPublic($slug)
{
    $folder = KegiatanFolder::where('slug', $slug)->where('is_public', 'PUBLIC')->firstOrFail();

    $folder->increment('views');

    return inertia('Kegiatan/ShowPublic', [
        'folder' => $folder,
        'files' => $folder->files()->get()
    ]);
}

public function sharedView($slug)
{
    $folder = KegiatanFolder::where('slug', $slug)->where('is_public', true)->firstOrFail();
    return inertia('Kegiatan/PublicView', [
        'folder' => $folder,
    ]);
}

public function toggleFavorite($id)
{

     try {
       $folder = KegiatanFolder::where('id', $id)->firstOrFail();
        $folder->is_favorite = !$folder->is_favorite;
        $folder->save();

        return response()->json([
            'is_favorite' => $folder->is_favorite,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}


public function updateShareStatus(string $slug, Request $request)
{
    $folder = KegiatanFolder::where('slug', $slug)->first();

    if (!$folder) {
        return response()->json(['message' => 'Folder not found'], 404);
    }

    $folder->is_public = $request->is_public;
    $folder->save();

    return response()->json([
        'message' => 'Status updated',
        'share_url' => $folder->is_public ? route('kegiatan.folders.share.link', $folder->slug) : null,
    ]);
}

public function togglePublic($id)
{
    try {
       $folder = KegiatanFolder::where('id', $id)->firstOrFail();
        $folder->is_public = !$folder->is_public;
        $folder->save();

        return response()->json([
            'is_public' => $folder->is_public,
            'slug' => $folder->slug,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

public function showSecret($slug_secret)
{
    $folder = KegiatanFolder::where('slug_secret', $slug_secret)->firstOrFail();

    $files = $folder->files()->get();

    $jumlah_unchecked = $folder->files()->where('checked', 0)->count();

    return inertia('Landing/ShowPublicSecret', [
        'folder' => $folder,
        'files' => $files,
        'jumlah_unchecked' => $jumlah_unchecked,
        'auth_user' => auth()->user(), // untuk cek login
    ]);
}


public function toggleSecret($id)
{

     try {
       $folder = KegiatanFolder::where('id', $id)->firstOrFail();
          if ($folder->is_secret) {
        $folder->is_secret = false;
        $folder->slug_secret = null;
    } else {
        $folder->is_secret = true;
        $folder->slug_secret = Str::random(16);
    }
        $folder->save();

        return response()->json([
        'is_secret' => $folder->is_secret,
        'slug_secret' => $folder->slug_secret,
    ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}

public function index(Request $request)
{
    $search = $request->input('search');

    // ukuran page: pertama 50 (default), request berikutnya boleh kirim ?per_page=15
    $perPage = (int) $request->get('per_page', 50);

    $query = KegiatanFolder::query()
        ->whereNull('parent_id')
        ->with(['children', 'user'])
        ->withCount('files')
        ->when($search, fn($q) => $q->where('judul', 'like', '%' . $search . '%'))
        ->when($request->user_id, fn($q, $v) => $q->where('user_id', $v))
        ->when($request->is_favorite == '1', fn($q) =>
            $q->where('is_favorite', true)->where('user_id', auth()->id())
        )
        ->when($request->is_public == '1', fn($q) => $q->where('is_public', true))
        ->when($request->filled('tanggal'), fn($q) => $q->whereDate('tanggal_kegiatan', $request->tanggal))
        ->when($request->filled('bulan'), fn($q) => $q->whereMonth('tanggal_kegiatan', $request->bulan))
        ->when($request->filled('tahun'), fn($q) => $q->whereYear('tanggal_kegiatan', $request->tahun))
        ->when($request->filled('pejabat_hadir'), fn($q) => $q->where('pejabat_hadir', $request->pejabat_hadir))
        ->orderBy('created_at', 'DESC');

    // ⬇️ pakai cursorPaginate + withQueryString supaya search/filter tetap nyantol
    $folders = $query->cursorPaginate($perPage)->withQueryString();

    if ($request->wantsJson()) {
        return response()->json([
            'folders' => $folders,
        ]);
    }

    $baseStats = KegiatanFolder::whereNull('parent_id');

    return inertia('Kegiatan/Index', [
        'folders' => $folders,
        'stats' => [
            'total'             => (clone $baseStats)->count(),
            'gubernur'          => (clone $baseStats)->where('pejabat_hadir', 'Gubernur')->count(),
            'wakil_gubernur'    => (clone $baseStats)->where('pejabat_hadir', 'Wakil Gubernur')->count(),
            'sekretaris_daerah' => (clone $baseStats)->where('pejabat_hadir', 'Sekretaris Daerah')->count(),
        ],
        'filters' => [
            'search' => $search,
            'user_id' => $request->user_id,
            'is_favorite' => $request->is_favorite,
            'is_public' => $request->is_public,
            'tanggal' => $request->tanggal,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
            'pejabat_hadir' => $request->pejabat_hadir,
            'users' => User::select('id', 'name')->where('staf','Staf Dokumentasi')->get(),
        ],
    ]);
}



public function loadMore(Request $request)
    {
        $query = KegiatanFolder::query();

        if ($request->has('search')) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        $folders = $query->latest()->paginate(12);

        return response()->json([
            'folders' => [
                'data' => $folders->items(),
                'links' => [
                    'next' => $folders->hasMorePages()
                        ? $folders->nextPageUrl()
                        : null,
                ],
            ]
        ]);
    }


public function store(Request $request)
{
    $request->validate([
        'judul' => 'required|string|max:255',
        'parent_id' => 'nullable|uuid|exists:kegiatan_folders,id',
        'tanggal_kegiatan' => 'required|date',
        'pejabat_hadir' => 'required|string|max:100'
    ]);

    $folder = KegiatanFolder::create([
        'id' => Str::uuid(),
        'user_id' => auth()->id(),
        'judul' => $request->judul,
        'tanggal_kegiatan' => $request->tanggal_kegiatan,
        'pejabat_hadir' => $request->pejabat_hadir,
        'parent_id' => $request->parent_id ?? null,
        'slug' => Str::slug($request['judul']) . '-' . Str::random(6),
        'is_public' => '0',
    ]);

    if ($request->expectsJson()) {
        // Ambil ulang agar include relasi (kalau nanti butuh)
        $folder->refresh(); // pastikan data dari DB terupdate penuh
        return response()->json(['subfolder' => $folder]);
    }
    // Kalau dari form biasa (Inertia form)
    return redirect()->route('kegiatan.folders.index')->with('success', 'Folder berhasil dibuat.');
}


}
