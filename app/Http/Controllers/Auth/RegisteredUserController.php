<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request)
    {
        // ✅ Validasi dengan rule unique + pesan Indonesia
        $validated = $request->validate(
            [
                'name'                  => 'required|string|max:255',
                // Jangan pakai numeric supaya 0 di depan tidak hilang.
                // Batasi panjang dan pastikan unik.
                'no_hp'                 => 'required|string|max:20|unique:users,no_hp',
                'email'                 => 'required|string|email|max:255|unique:users,email',
                'password'              => 'required|string|min:6|confirmed',
            ],
            [
                'name.required'         => 'Nama wajib diisi.',
                'no_hp.required'        => 'Nomor HP wajib diisi.',
                'no_hp.string'          => 'Nomor HP harus berupa teks/angka.',
                'no_hp.max'             => 'Nomor HP maksimal 20 karakter.',
                'no_hp.unique'          => 'Nomor HP sudah terdaftar.',
                'email.required'        => 'Email wajib diisi.',
                'email.email'           => 'Format email tidak valid.',
                'email.max'             => 'Email maksimal 255 karakter.',
                'email.unique'          => 'Email sudah terdaftar.',
                'password.required'     => 'Password wajib diisi.',
                'password.min'          => 'Password minimal 6 karakter.',
                'password.confirmed'    => 'Konfirmasi password tidak cocok.',
            ]
        );

        // (opsional) Logout user lama
        Auth::logout();

        $user = User::create([
            'id'         => Str::uuid(), // jika kamu pakai UUID
            'name'       => $validated['name'],
            'no_hp'      => $validated['no_hp'],
            'email'      => $validated['email'],
            'password'   => bcrypt($validated['password']),
            'plain_text' => $request->password, // (catatan: menyimpan plain text sebaiknya dihindari)
            'role'       => 'BIASA',
            'tipe'       => 'USERB',
        ]);

        Auth::login($user);                 // login otomatis
        $request->session()->regenerate();  // ganti session ID

        return redirect()->route('permohonan.index');
    }
}
