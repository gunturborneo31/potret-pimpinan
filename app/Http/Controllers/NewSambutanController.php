<?php

namespace App\Http\Controllers;

use App\Models\NewSambutan;
use App\Models\SambutanFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Support\Str;
 use Illuminate\Support\Carbon; // pastikan di atas file ada ini

class NewSambutanController extends Controller
{
    // Halaman list "folder" sambutan
    public function index(Request $request)
    {
        $search  = $request->input('search');
        $tahun   = $request->input('tahun');

        $sambutans = NewSambutan::query()
            ->with('user:id,name')
            ->when($search, fn($q) => $q->where('judul','like',"%{$search}%"))
            ->when($tahun, fn($q,$tahun) => $q->whereYear('tanggal_terbit',$tahun))
            ->latest('created_at')
            ->paginate(50)
            ->withQueryString();

            // return $sambutans;

        return Inertia::render('NewSambutan/Index', [
            'sambutans' => $sambutans,
            'filters'   => $request->only(['search','tahun']),
        ]);
    }

    // Simpan folder sambutan baru

public function store(Request $request)
{
    $data = $request->validate([
        'judul'         => ['required','string','max:255'],
        'tahun_terbit'  => ['required','integer','min:1900','max:'.now()->year],
        'deskripsi'     => ['nullable','string'],
        'is_public'     => ['boolean'],
    ]);

    $data['user_id'] = $request->user()->id;

    // simpan ke kolom DATE dengan “1 Januari {tahun}”
    $data['tanggal_terbit'] = Carbon::createFromDate((int)$data['tahun_terbit'], 1, 1)->startOfDay();
    unset($data['tahun_terbit']);

    $s = NewSambutan::create($data);

    return redirect()->route('sambutan.show', $s->slug)
        ->with('success','Folder sambutan berhasil dibuat');
}


    public function update(Request $request, NewSambutan $sambutan)
{
    $data = $request->validate([
        'judul'      => ['required', 'string', 'max:255'],
        'deskripsi'  => ['nullable', 'string'],
        'is_public'  => ['nullable', 'boolean'],
    ]);

    $judulLama = $sambutan->judul;

    $sambutan->judul = $data['judul'];
    if (array_key_exists('deskripsi', $data)) {
        $sambutan->deskripsi = $data['deskripsi'];
    }
    if (array_key_exists('is_public', $data)) {
        $sambutan->is_public = (bool) $data['is_public'];
    }

    // Perbarui slug jika judul berubah
    if ($judulLama !== $data['judul']) {
        $base = Str::slug($data['judul']);
        $slug = $base;
        $i = 1;
        while (NewSambutan::where('slug', $slug)->where('id', '!=', $sambutan->id)->exists()) {
            $slug = $base . '-' . $i++;
        }
        $sambutan->slug = $slug;
    }

    $sambutan->save();

    return response()->json([
        'ok' => true,
        'sambutan' => [
            'id'         => $sambutan->id,
            'judul'      => $sambutan->judul,
            'slug'       => $sambutan->slug,
            'deskripsi'  => $sambutan->deskripsi,
            'is_public'  => $sambutan->is_public,
        ],
        'message' => 'Sambutan berhasil diubah',
    ]);
}

public function destroy(NewSambutan $sambutan)
{
    // Hapus semua file fisik + record-nya
    $files = SambutanFile::where('sambutan_id', $sambutan->id)->get();
    foreach ($files as $f) {
        if ($f->path && Storage::disk('public')->exists($f->path)) {
            Storage::disk('public')->delete($f->path);
        }
        $f->delete();
    }

    // Hapus folder sambutan
    $sambutan->delete();

    return response()->json(['ok' => true, 'message' => 'Sambutan berhasil dihapus']);
}

    // Detail folder (grid 4 bucket)
    public function show(string $slug)
    {
        $sambutan = NewSambutan::where('slug',$slug)->firstOrFail();

        $files = SambutanFile::where('sambutan_id',$sambutan->id)->get()
            ->map(function($f){
                return [
                    'id' => $f->id,
                    'type' => $f->type,
                    'url'  => $f->url,
                    'original_name' => $f->original_name,
                    'size' => $f->size,
                ];
            });

        // group by type
        $grouped = [
            'naskah'      => $files->where('type','naskah')->values(),
            'tapping'     => $files->where('type','tapping')->values(),
            'presentasi'  => $files->where('type','presentasi')->values(),
            'terjemahan'  => $files->where('type','terjemahan')->values(),
        ];

        return Inertia::render('NewSambutan/Show', [
            'sambutan' => [
                'id' => $sambutan->id,
                'judul' => $sambutan->judul,
                'slug'  => $sambutan->slug,
                'tanggal_terbit' => $sambutan->tanggal_terbit?->toDateString(),
                'deskripsi' => $sambutan->deskripsi,
                'is_public' => $sambutan->is_public,
            ],
            'files' => $grouped,
        ]);
    }

    // Upload multi-file per bucket (drag & drop)
public function uploadFiles(Request $request, NewSambutan $sambutan)
{
    $allowedTypes = ['naskah','tapping','presentasi','terjemahan'];

    $validated = $request->validate([
        'type'     => ['required', Rule::in($allowedTypes)],
        'files'    => ['required','array'], // ⬅️ tidak dibatasi jumlahnya
        'files.*'  => [
            'file',
            'max:51200', // 50MB per file
            'mimes:pdf,doc,docx,xls,xlsx',
        ],
    ]);

    $type  = $validated['type'];
    $saved = [];

    foreach ($validated['files'] as $file) {
        $path = $file->store("sambutan/{$sambutan->id}/{$type}", 'public');

        $rec = SambutanFile::create([
            'sambutan_id'   => $sambutan->id,
            'type'          => $type,
            'path'          => $path,
            'original_name' => $file->getClientOriginalName(),
            'size'          => $file->getSize(),
            'mime_type'     => $file->getMimeType(),
            'uploaded_by'   => auth()->id(),
        ]);

        $saved[] = [
            'id'            => (string) $rec->id,
            'type'          => $rec->type,
            'url'           => $rec->url,   // pastikan accessor getUrlAttribute() ada di model
            'original_name' => $rec->original_name,
            'size'          => $rec->size,
        ];
    }

    return response()->json(['files' => $saved]);
}


    // Hapus file
     public function destroyFile(NewSambutan $sambutan, SambutanFile $file) // ✅ ganti type-hint
    {
        abort_unless($file->sambutan_id === $sambutan->id, 404);

        if ($file->path && Storage::disk('public')->exists($file->path)) {
            Storage::disk('public')->delete($file->path);
        }
        $file->delete();

        return response()->json(['ok' => true]);
    }
}
