<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\FileUpload;

class FileController extends Controller
{
    public function createFolder(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $folder = Folder::create([
            'nama' => $request->nama,
            'user_id' => auth()->id(),
        ]);

        return response()->json($folder);
    }

    public function uploadFile(Request $request)
    {
       $file = $request->file('file');

$path = $file->store('uploads', 'public');


$upload = FileUpload::create([
    'folder_id'      => $request->folder_id,
    'file_path'      => $file->store('uploads', 'public'),  // disimpan di storage/public/uploads
    'original_name'  => $file->getClientOriginalName(),
    'file_type'      => $file->getClientMimeType(),  // contoh: application/pdf
    'file_size'      => $file->getSize(),            // ukuran dalam byte
]);


    }

    public function index()
    {
        $folders = Folder::with('files')->where('user_id', auth()->id())->get();
        return response()->json($folders);
    }
}
