<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KegiatanFolder;
use App\Models\KegiatanFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KegiatanFileController extends Controller
{
public function index(Request $request, $slug)
{
    $folder = KegiatanFolder::where('slug', $slug)->firstOrFail();

    $files = KegiatanFile::where('kegiatan_folder_id', $folder->id)
        ->latest()
        ->paginate(10); // Bisa diubah jumlah per page

    return response()->json($files);
}

public function indexmovecopy(Request $request)
{
    $folders = KegiatanFolder::whereNull('parent_id')->get(); // hanya folder utama
    return response()->json($folders);
}


public function store(Request $request, $slug)
{
    $folder = KegiatanFolder::where('slug', $slug)->firstOrFail();

    $request->validate([
        'file' => 'required|file|max:2048000' // max 2GB in kilobytes (2048000 KB)
    ]);

    $file = $request->file('file');
    $originalName = $file->getClientOriginalName();
    $size = $file->getSize();

    // Simpan ke storage
    $path = $file->store('kegiatan/' . $folder->slug, 'public');

    // Simpan ke DB
    $kegiatanFile = KegiatanFile::create([
        'kegiatan_folder_id' => $folder->id,
        'nama_file' => $originalName,
        'path' => $path,
        'size' => $size,
        'checked' => false,
        'jumlah_download' => 0,
    ]);

    $kegiatanFile->load('folder');
    
    return response()->json([
        'message' => 'Upload berhasil',
        'file' => $kegiatanFile,
    ]);
}

public function destroy(KegiatanFile $file)
{
    try {
        // Hapus file dari storage
        if (Storage::exists($file->path)) {
            Storage::delete($file->path);
        }

        // Hapus dari database
        $file->delete();

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => 'Gagal menghapus file'], 500);
    }
}

public function move(Request $request, $id)
{
    $file = KegiatanFile::findOrFail($id);
    $targetFolder = KegiatanFolder::findOrFail($request->target_folder_id);

    $file->kegiatan_folder_id = $targetFolder->id;
    $file->save();

    return response()->json([
        'message' => 'File berhasil dipindahkan',
        'slug' => $targetFolder->slug, // untuk redirect
    ]);
}


public function copy(Request $request, $id)
{
    $file = KegiatanFile::findOrFail($id);
    $targetFolder = KegiatanFolder::findOrFail($request->target_folder_id);

    $newFile = $file->replicate();
    $newFile->kegiatan_folder_id = $targetFolder->id;
    $newFile->save();

    return response()->json([
        'message' => 'File berhasil disalin',
        'slug' => $targetFolder->slug,
    ]);
}



public function update(Request $request, KegiatanFile $file)
{
    $request->validate([
        'checked' => 'required|boolean',
    ]);

    $file->update([
        'checked' => $request->checked,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Status file berhasil diperbarui.',
    ]);
}

public function toggleChecked($id)
{
    try {
        $file = KegiatanFile::findOrFail($id);
        $file->checked = !$file->checked;
        $file->save();

        return response()->json([
            'checked' => $file->checked,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ], 500);
    }
}


}
