<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
  public function index(Request $request)
{
    $search = $request->input('search');

    $users = User::query()
        ->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('no_hp', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        })
        ->orderBy('created_at', 'desc')
        ->paginate(10)
        ->withQueryString();

    return Inertia::render('Users/Index', [
        'users' => $users,
        'filters' => [
            'search' => $search,
        ],
    ]);
}


    public function create()
    {
        return Inertia::render('Users/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:SUPERADMIN,STAFF,BIASA',
            'password' => 'required|min:6',
        ]);

        User::create([
            'name' => $request->name,
            'no_hp' => $request->no_hp,
            'email' => $request->email,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit', [
            'user' => $user,
        ]);
    }


    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'no_hp' => 'required|string|max:20',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:SUPERADMIN,STAFF,BIASA',
            'password' => 'nullable|string|min:6',
        ]);

        $data = $request->only('name', 'no_hp', 'email', 'role');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }


    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }
}
