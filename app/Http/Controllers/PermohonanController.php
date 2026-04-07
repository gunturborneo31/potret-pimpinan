<?php

namespace App\Http\Controllers;

use App\Models\Permohonan;
use App\Models\Komentar;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Notifications\PermohonanCreated;
use App\Notifications\PermohonanUserCreated;
use App\Notifications\PermohonanDisposisiUpdated;
use Illuminate\Support\Facades\Notification;

class PermohonanController extends Controller
{
public function index(Request $request)
{
    $user = auth()->user();

    $query = Permohonan::with('user','disposisi')->latest();

    // ✅ Role filtering
    if ($user->role === 'STAFF') {
        // Hanya permohonan yang sudah didisposisikan ke staff ini
        $query->where('disposisi', $user->id);
    }

    if ($user->role === 'BIASA') {
        // Hanya permohonan yang dibuat oleh user ini
        $query->where('user_id', $user->id);
    }

    // ✅ Filter berdasarkan inputan pencarian
    if ($request->filled('search')) {
        $query->where('title', 'like', '%' . $request->search . '%');
    }

    if ($request->filled('user')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->user . '%');
        });
    }

    if ($request->filled('kategori')) {
        $query->where('kategori', $request->kategori);
    }

    if ($request->filled('priority')) {
        $query->where('priority', $request->priority);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // ✅ Pagination
    $permohonans = $query->paginate(25)->withQueryString();

    return Inertia::render('Permohonan/Index', [
        'permohonans' => $permohonans,
        'filters' => $request->only('search', 'user', 'kategori', 'priority', 'status'),
        'user' => $user,
    ]);

    
}

    public function show($id)
{
        $permohonan = Permohonan::with([
            'komentars' => fn ($q) => $q->latest(), // Order by terbaru
            'komentars.user'
        ])->findOrFail($id);
    return inertia('Permohonan/Show', [
        'permohonan' => $permohonan,
        'user' => auth()->user(),
    ]);
}


public function create()
{
    return Inertia::render('Permohonan/Create', [
        'user' => Auth::user(),
        'staffList' => User::where('role', 'STAFF')->get(),
    ]);
}
    // ✅ Menyimpan permohonan baru
    public function store(Request $request)
    {
        $request->validate([
        'kategori'    => 'required|string',
        'title'       => 'required|string|max:255',
        'description' => 'required|string',
        'priority'    => 'required|in:Critical/Urgent,Medium,Low',
        'file'        => 'nullable|file|max:10240', // 10MB
        'disposisi'   => 'nullable|exists:users,id',
    ]);

    $path = null;
    if ($request->hasFile('file')) {
        $path = $request->file('file')->store('permohonan_files', 'public');
    }

    $permohonan = Permohonan::create([
        'id'          => Str::uuid(),
        'user_id'     => auth()->id(),
        'kategori'    => $request->kategori,
        'title'       => $request->title,
        'description' => $request->description,
        'priority'    => $request->priority,
        'file'        => $path,
        'disposisi'   => $request->disposisi,
        'status'      => 'Diajukan',
    ]);
   
    // Kirim notifikasi ke semua SUPERADMIN
    $superadmins = User::where('role', 'SUPERADMIN')->get();
    foreach ($superadmins as $admin) {
        $admin->notify(new PermohonanCreated($permohonan));
    }


    return redirect()->route('permohonan.index')->with('success', 'Permohonan berhasil dikirim.');
    
}

public function edit($id)
{
    // $permohonan = Permohonan::with('user')->findOrFail($id);
    // $permohonan = Permohonan::with(['komentars.user'])->findOrFail($id);
    $permohonan = Permohonan::with([
    'komentars' => fn ($q) => $q->latest(), // Order by terbaru
    'komentars.user'
])->findOrFail($id);
// return $permohonan;
    $staffList = User::where('role', 'STAFF')->get();
    $user = Auth::user();

    return Inertia::render('Permohonan/Edit', [
        'permohonan' => $permohonan,
        'staffList' => $staffList,
        'user' => $user,
    ]);
}

public function update(Request $request, $id)
{
    $request->validate([
        'title' => 'required',
        'kategori' => 'required',
        'priority' => 'required',
        'description' => 'required',
        'disposisi' => 'nullable|exists:users,id',
    ]);

    $permohonan = Permohonan::findOrFail($id);

    $permohonan->update([
        'title' => $request->title,
        'kategori' => $request->kategori,
        'priority' => $request->priority,
        'description' => $request->description,
        'disposisi' => $request->disposisi,
    ]);


        if ($request->disposisi) {
        $disposisiUser = User::find($request->disposisi);
        if ($disposisiUser) {
            $disposisiUser->notify(new PermohonanCreated($permohonan));
        }
    }

   // Kirim ke pemohon (user yang membuat permohonan)
    $pemohon = User::find($permohonan->user_id);
    if ($pemohon) {
        $pemohon->notify(new PermohonanCreated($permohonan));
    }

    return redirect()->route('permohonan.index')->with('success', 'Permohonan berhasil diperbarui.');
}

public function storeKomentar(Request $request, $id)
{
    $request->validate([
        'komentar' => 'required|string',
        'file' => 'nullable|file|max:2048',
    ]);

    $path = null;
    if ($request->hasFile('file')) {
        $path = $request->file('file')->store('komentar_files', 'public');
    }

    Komentar::create([
        'permohonan_id' => $id,
        'user_id' => auth()->id(),
        'komentar' => $request->komentar,
        'file' => $path,
    ]);

    return back();
}

public function komentarTerbaru($id)
{
    return Komentar::with('user')->where('permohonan_id', $id)->latest()->first();
}

public function updateStatus(Request $request, $id)
{
    $request->validate([
        'status' => 'required|in:Diajukan,Diproses,Selesai,Ditolak'
    ]);

    $permohonan = Permohonan::findOrFail($id);
    $permohonan->status = $request->status;
    $permohonan->save();

    return back(); // atau response()->json(['success' => true]);
}

public function destroy($id)
{
    $user = auth()->user();

    if (!in_array($user->role, ['SUPERADMIN', 'STAFF'])) {
        abort(403, 'Unauthorized');
    }

    $permohonan = Permohonan::findOrFail($id);
    $permohonan->delete();

    return redirect()->route('permohonan.index')->with('message', 'Permohonan berhasil dihapus.');
}


}

