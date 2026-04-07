<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\User;
use App\Models\KegiatanFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class BeritaController extends Controller
{


    public function __construct()
{
    $this->middleware('auth');
}

public function index(Request $request)
{
    // Query awal dengan relasi penulis dan editor
    $query = Berita::with(['penulis', 'editor']);

    // Filter pencarian judul
    if ($request->search) {
        $query->where('judul', 'like', '%' . $request->search . '%');
    }
    
    // Filter status (publik / tidak)
if ($request->filled('status')) {
    if ($request->status === '1') {
        $query->where('is_public', 1);
    }
    // Jika status "0" (false), jangan filter sama sekali
}

    if ($request->bulan) {
        $query->whereMonth('tanggal_terbit', $request->bulan);
    }

    if ($request->tahun) {
        $query->whereYear('tanggal_terbit', $request->tahun);
    }

    if ($request->filled('penulis')) {
        $query->whereIn('penulis_id', (array) $request->penulis);
    }


    // Ambil berita yang difilter dan paginasi
    $beritas = $query->latest()->paginate(100)->withQueryString();

    // Ambil UUID dari kolom lainnya_id (json array) dan kumpulkan semua ID unik
    $kontributorIds = collect($beritas->items())
        ->pluck('lainnya_id')             // Ambil semua kolom lainnya_id
        ->filter()                        // Buang yang null
        ->flatMap(function ($ids) {       // Gabungkan semua array json
            return is_array($ids)
                ? $ids
                : json_decode($ids, true) ?? [];
        })
        ->unique()
        ->values()
        ->all();

    // Query data user berdasarkan UUID kontributor
    $kontributors = User::whereIn('id', $kontributorIds)
        ->get()
        ->keyBy('id'); // agar bisa diakses langsung dengan `kontributors[uuid].name`
    
    $penulisIds = $beritas->pluck('penulis_id')->filter()->unique()->values()->all();

    // Ambil data user penulis
    $penulisList = User::where('tipe', 'Staf Berita')->orwhere('tipe', 'Admin Berita')->orwhere('staf', 'Staf Berita')->get();

    // return $penulisList; 

    // Kirim ke halaman Index.vue
    return Inertia::render('Berita/Index', [
        'beritas' => $beritas,
        'filters' => $request->only(['search', 'status', 'bulan', 'tahun']),
        'penulisList' => $penulisList,
        'kontributors' => $kontributors,
    ]);
}



    public function create()
    {       
        return Inertia::render('Berita/Create', [
            'users' => User::where('role','STAFF')->select('id', 'name')->get(),
            'penulisid' => User::where('tipe','Staf Berita')->orwhere('tipe','Admin Berita')->orwhere('staf','Staf Berita')->orwhere('staf2', 'Staf Berita')->select('id', 'name')->get(),
            'user' => Auth::user(),
            'kegiatans' => KegiatanFolder::select('id', 'judul')->get(),
        ]);
    }


public function store(Request $request)
{
     $request->validate([
        'judul' => 'required|string|max:255',
        'slug' => 'nullable|string|max:255|unique:beritas,slug',
        'isi_berita' => 'required|string',
        'tanggal_terbit' => 'required|date',
        'penulis_id' => 'required|exists:users,id',
        'editor_id' => 'nullable|exists:users,id',
        'lainnya_id' => 'nullable|array',
        'lainnya_id.*' => 'exists:users,id',
        'kegiatan_id' => 'nullable|exists:kegiatan_folders,id',
        'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10048',
        'file' => 'nullable|file|max:20408',
        'is_public' => 'nullable|boolean',
    ]);

    // Simpan file jika ada
    $path = null;
    if ($request->hasFile('file')) {
        $path = $request->file('file')->store('berita_files', 'public');
    }

    $thumbnailPath = null;
    if ($request->hasFile('thumbnail')) {
        $thumbnailPath = $request->file('thumbnail')->store('berita_thumbnails', 'public');
    }


    // Buat slug jika tidak ada
    $slug = $request->slug ?? Str::slug($request->judul) . '-' . Str::random(4);

    // Buat record
    Berita::create([
        'id' => Str::uuid(),
        'user_id' => auth()->id(), // User login
        'judul' => $request->judul,
        'slug' => $slug,
        'isi_berita' => $request->isi_berita,
        'tanggal_terbit' => $request->tanggal_terbit,
        'penulis_id' => $request->penulis_id,
        'editor_id' => $request->editor_id,
        // 'lainnya_id' => json_encode($request->lainnya_id ?? []),
        'lainnya_id' => $request->lainnya_id ?? [],
        'kegiatan_id' => $request->kegiatan_id,
        'thumbnail' => $thumbnailPath,
        'file' => $path,
        'is_public' => $request->has('is_public') ? (bool)$request->is_public : false,
    ]);

    return redirect()->route('berita.index')->with('success', 'Berita berhasil disimpan.');
}


public function show($id)
{
    // Ambil data berita
    $berita = Berita::with(['penulis', 'editor', 'kegiatan'])->findOrFail($id);

    // Ambil data kontributor berdasarkan array lainnya_id (nullable)
    $kontributors = User::whereIn('id', $berita->lainnya_id ?? [])->get()->keyBy('id');

    // Kirim data ke frontend
    return Inertia::render('Berita/Show', [
        'berita' => $berita,
        'kontributors' => $kontributors,
    ]);
}

public function edit($id)
{
    $berita = Berita::findOrFail($id);

    return Inertia::render('Berita/Edit', [
        'berita' => $berita->only([
            'id',
            'judul',
            'slug',
            'isi_berita',
            'tanggal_terbit',
            'penulis_id',
            'editor_id',
            'lainnya_id',
            'kegiatan_id',
            'is_public',
        ]),
        'users' => User::where('role','STAFF')->select('id', 'name')->get(),
        'penulisid' => User::where('tipe','Staf Berita')->orwhere('tipe','Admin Berita')->orwhere('staf','Staf Berita')->select('id', 'name')->get(),
        'kegiatans' => KegiatanFolder::select('id', 'judul')->get(),
    ]);
}


public function update(Request $request, $id)
{

    $request->validate([
        'judul' => 'required|string|max:255',
        'isi_berita' => 'required|string',
        'tanggal_terbit' => 'required|date',
        'penulis_id' => 'required|exists:users,id',
        'editor_id' => 'required|exists:users,id',
        'lainnya_id' => 'nullable|array',
        'lainnya_id.*' => 'exists:users,id',
        'kegiatan_id' => 'nullable|exists:kegiatan_folders,id',
        'thumbnail' => 'nullable|image|max:2048',
        'file' => 'nullable|file|max:10240',
        'is_public' => 'boolean',
    ]);

    $berita = Berita::findOrFail($id);

    // Handle array lainnya_id (simpan sebagai JSON jika kolomnya JSON atau TEXT)
    // $berita->lainnya_id = $validated['lainnya_id'] ?? [];

    // Handle file upload
    if ($request->hasFile('thumbnail')) {
        $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
        $berita->thumbnail = $thumbnailPath;
    }

    if ($request->hasFile('file')) {
        $filePath = $request->file('file')->store('berita_files', 'public');
        $berita->file = $filePath;
    }


    $berita->update([
       'judul' => $request->judul,
       'slug' => \Str::slug($request->judul),
       'isi_berita' => $request->isi_berita,
       'tanggal_terbit' => $request->tanggal_terbit,
       'penulis_id' => $request->penulis_id,
       'editor_id' => $request->editor_id,
       'lainnya_id' => $request->lainnya_id ?? [],
       'kegiatan_id' => $request->kegiatan_id ?? null,
       'is_public' => $request->boolean('is_public')
    ]);

    return redirect()->route('berita.index')->with('success', 'Berita berhasil diperbarui.');
}


 public function destroy($id)
{
    Berita::findOrFail($id)->delete();
    return response()->json(['success' => 'Berita berhasil dihapus']);
}

public function tampilkan($id)
{
    $berita = Berita::findOrFail($id);
    $berita->is_public = 1;
    $berita->save();

    return response()->json(['success' => 'Berita berhasil dipublish']);

}

public function sembunyikan($id)
{
    $berita = Berita::findOrFail($id);
    $berita->is_public = 0;
    $berita->save();

    return response()->json(['success' => 'Berita berhasil diraft']);
}

}
