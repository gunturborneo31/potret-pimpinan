<?php

namespace App\Http\Controllers;

use App\Models\Komentar;
use Illuminate\Http\Request;

class KomentarController extends Controller
{
    public function store(Request $request, $permohonanId)
    {
        $request->validate([
            'komentar' => 'required|string',
            'file' => 'nullable|file|max:10240',
        ]);

        $komentar = new Komentar();
        $komentar->permohonan_id = $permohonanId;
        $komentar->user_id = auth()->id();
        $komentar->komentar = $request->komentar;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/komentar', $filename);

            $komentar->file = 'komentar/' . $filename;
            $komentar->file_original_name = $file->getClientOriginalName(); // ✔⃣ Nama asli file
        }

        $komentar->save();

        return redirect()->back()->with('success', 'Komentar berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $komentar = Komentar::findOrFail($id);

        if (auth()->user()->role === 'SUPERADMIN' || auth()->id() === $komentar->user_id) {
            $komentar->delete();
            return redirect()->back()->with('success', 'Komentar berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Tidak memiliki izin untuk menghapus komentar ini.');
    }
}
