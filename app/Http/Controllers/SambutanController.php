<?php

namespace App\Http\Controllers;
namespace App\Http\Controllers;

use App\Models\Sambutan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SambutanController extends Controller
{


    public function index()
{
    $sambutans = Sambutan::with('user')->latest()->get();
    // Ambil semua UUID kontributor dari semua sambutan
    $kontributorIds = $sambutans->pluck('kontributor_id') // ambil array-array
        ->filter() // buang null
        ->flatten() // rata jadi 1 dimensi
        ->unique() // tidak duplikat
        ->values(); // reset index

    $kontributors = User::whereIn('id', $kontributorIds)->get()->keyBy('id');

    return Inertia::render('Sambutan/Index', [
        'sambutans' => $sambutans,
        'kontributors' => $kontributors,
    ]);
}


    public function create()
    {
        return Inertia::render('Sambutan/Create', [
        'users' => User::where('role', 'STAFF')->select('id', 'name')->get(),
    ]);
    }

    public function store(Request $request)
    {
    $request->validate([
        'judul' => 'required|string|max:255',
        'tanggal_dibuat' => 'required|date',
        'isi_sambutan' => 'required|string',
        'kontributor_id' => 'nullable|array',
        'kontributor_id.*' => 'exists:users,id',
        'file' => 'nullable|file|max:10240', // max 10MB
        'is_public' => 'boolean',
    ]);

    $filePath = null;

    if ($request->hasFile('file')) {
        $file = $request->file('file');
        // $filePath = $file->store('sambutan-files', 'public');
        $filePath = $file->store('/sambutan', 'public');

    }

    $sambutan = Sambutan::create([
        'id' => Str::uuid(),
        'judul' => $request->judul,
        'tanggal_dibuat' => $request->tanggal_dibuat,
        'slug' => \Str::slug($request->judul) . '-' . Str::random(4),
        'isi_sambutan' => $request->isi_sambutan,
        'file' => $filePath,
        'is_public' => $request->boolean('is_public'),
        'user_id' => auth()->id(),
        'kontributor_id' => $request->kontributor_id ?? [],

    ]);

    return redirect()->route('sambutan.index')->with('success', 'Sambutan berhasil dibuat.');

}

    public function edit($id)
    {

        $sambutan = Sambutan::findOrFail($id);
        return Inertia::render('Sambutan/Edit', [
            'sambutan' => $sambutan->only([
                'id',
                'judul',
                'tanggal_dibuat',
                'slug',
                'isi_sambutan',
                'kontributor_id',
                'is_public',
            ]),
            'users' => User::where('role','STAFF')->select('id', 'name')->get(),
        ]);
    }

public function update(Request $request, $id)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'tanggal_dibuat' => 'required|date',
            'isi_sambutan' => 'required|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'is_public' => 'boolean',
            'kontributor_id' => 'nullable|array',
            'kontributor_id.*' => 'exists:users,id',
        ]);

        $sambutan = Sambutan::findOrFail($id);


        if ($request->hasFile('file')) {
            if ($sambutan->file) {
                Storage::delete($sambutan->file);
            }
            $sambutan->file = $request->file('file')->store('uploads/sambutan');
        }

        $sambutan->update([
            'judul' => $request->judul,
            'tanggal_dibuat' => $request->tanggal_dibuat,
            'isi_sambutan' => $request->isi_sambutan,
            'is_public' => $request->is_public ?? false,
            'kontributor_id' => $request->kontributor_id ?? [],

        ]);

        return redirect()->route('sambutan.index')->with('success', 'Sambutan berhasil diperbarui.');
    }

public function show($slug)
{
    $sambutan = Sambutan::with('user')->where('slug', $slug)->firstOrFail();

    $kontributorIds = collect($sambutan->kontributor_id)
        ->filter()
        ->unique()
        ->values();

    $kontributors = User::whereIn('id', $kontributorIds)->get();

    return inertia('Sambutan/Show', [
        'sambutan' => [
            ...$sambutan->toArray(),
            'kontributor' => $kontributors,
        ],
    ]);
}


 public function destroy($id)
{
    Sambutan::findOrFail($id)->delete();
    return redirect()->route('sambutan.index')->with('success', 'Sambutan berhasil dihapus.');
}



}
