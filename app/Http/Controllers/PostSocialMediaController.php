<?php

namespace App\Http\Controllers;

use App\Models\PostSocialMedia;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostSocialMediaController extends Controller
{
public function index(Request $request)
{
    $query = PostSocialMedia::with('user')->orderBy('created_at','DESC');
    // Filter berdasarkan user (penulis)
    if ($request->has('penulis') && is_array($request->penulis) && count($request->penulis) > 0) {
        $query->whereIn('user_id', $request->penulis);
    }

    // Filter search judul (optional)
    if ($request->has('search') && $request->search != '') {
        $query->where('judul', 'like', '%' . $request->search . '%');
    }

    $posts = $query->paginate(50)->withQueryString();

    // Ambil user dari hasil post saat ini (terfilter)
    $users = $posts->pluck('user')->unique('id')->mapWithKeys(function ($user) {
        return [$user->id => ['name' => $user->name]];
    });

    // Semua user STAFF untuk select dropdown
    $userselect = User::where('role', 'STAFF')->get(['id', 'name']);

    // Kirim filters kembali ke frontend agar selectedUsers sync
    $filters = [
        'penulis' => $request->penulis ?? [],
        'search' => $request->search ?? '',
    ];

    return Inertia::render('PostSocialMedia/Index', [
        'posts' => $posts,
        'users' => $users,
        'userselect' => $userselect,
        'filters' => $filters,
    ]);
}


public function create()
    {
        $options = [
            'Instagram Reels',
            'Instagram Post',
            'Youtube Video',
            'Youtube Short',
            'Tiktok',
            'Facebook Post',
        ];

        return Inertia::render('PostSocialMedia/Create', [
            'options' => $options,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'link' => 'required|url',
            'option' => 'required|in:Instagram Reels,Instagram Post,Youtube Video,Youtube Short,Tiktok,Facebook Post',
        ]);

        PostSocialMedia::create([
            'id' => Str::uuid(),
            'judul' => $request->judul,
            'link' => $request->link,
            'option' => $request->option,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('post-sosmed.index')->with('success', 'Post berhasil dibuat.');
    }

      public function edit($id)
    {
        $post = PostSocialMedia::findOrFail($id);
        $users = \App\Models\User::select('id', 'name')->get(); // kalau perlu user list

        return inertia('PostSocialMedia/Edit', [
            'post' => $post,
            'users' => $users->keyBy('id'), // buat object dengan key id
        ]);
    }

    // Fungsi update: untuk simpan data edit
    public function update(Request $request, $id)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'link' => 'required|url|max:255',
            'option' => 'required|in:Instagram Reels,Instagram Post,Youtube Video,Youtube Short,Tiktok,Facebook Post',
            'user_id' => 'required|exists:users,id',
        ]);

        $post = PostSocialMedia::findOrFail($id);
        $post->update($request->only(['judul', 'link', 'option', 'user_id']));

        return redirect()->route('post-sosmed.index')->with('success', 'Post berhasil diperbarui!');
    }

    public function destroy($id)
{
    $post = PostSocialMedia::findOrFail($id);
    $post->delete();

    return redirect()->route('post-sosmed.index')->with('success', 'Post berhasil dihapus!');
}


}
