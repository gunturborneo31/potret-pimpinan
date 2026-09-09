<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Permohonan;
use App\Models\Berita;
use App\Models\KegiatanFolder;
use App\Models\KegiatanFile;
use App\Models\NewSambutan;
use App\Models\SambutanFile;
use App\Models\PostSocialMedia;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Buat beberapa User =====
        $users = [];
        for ($i = 1; $i <= 3; $i++) {
            $users[] = User::create([
                'name' => "Staff $i",
                'email' => "staff$i@example.com",
                'no_hp' => '08123456789' . $i,
                'role' => 'STAFF',
                'password' => Hash::make('password'),
            ]);
        }

        // User BIASA
        for ($i = 1; $i <= 5; $i++) {
            User::create([
                'name' => "User Biasa $i",
                'email' => "user$i@example.com",
                'no_hp' => '08987654321' . $i,
                'role' => 'BIASA',
                'password' => Hash::make('password'),
            ]);
        }

        // ===== Buat Permohonan =====
        $permohonanData = [
            ['title' => 'Permohonan Sambutan 1', 'kategori' => 'Sambutan', 'status' => 'Diajukan', 'priority' => 'Low'],
            ['title' => 'Permohonan Sambutan 2', 'kategori' => 'Sambutan', 'status' => 'Diproses', 'priority' => 'Medium'],
            ['title' => 'Permohonan Dokumentasi 1', 'kategori' => 'Dokumentasi', 'status' => 'Selesai', 'priority' => 'Critical/Urgent'],
            ['title' => 'Permohonan Lainnya 1', 'kategori' => 'Lainnya', 'status' => 'Ditolak', 'priority' => 'Medium'],
            ['title' => 'Permohonan Sambutan 3', 'kategori' => 'Sambutan', 'status' => 'Diajukan', 'priority' => 'Low'],
            ['title' => 'Permohonan Dokumentasi 2', 'kategori' => 'Dokumentasi', 'status' => 'Diproses', 'priority' => 'Medium'],
        ];

        foreach ($permohonanData as $data) {
            Permohonan::create([
                'user_id' => $users[0]->id,
                'title' => $data['title'],
                'kategori' => $data['kategori'],
                'status' => $data['status'],
                'priority' => $data['priority'],
                'description' => 'Deskripsi ' . $data['title'],
            ]);
        }

        // ===== Buat Berita =====
        $beritaData = [
            'Berita 1 - Kegiatan Sosial',
            'Berita 2 - Pembukaan Acara',
            'Berita 3 - Koordinasi Tim',
            'Berita 4 - Laporan Bulanan',
            'Berita 5 - Testimoni Mitra',
        ];

        foreach ($beritaData as $i => $judul) {
            Berita::create([
                'user_id' => $users[$i % count($users)]->id,
                'judul' => $judul,
                'slug' => Str::slug($judul) . '-' . time() . $i,
                'isi' => 'Konten berita ' . $judul,
                'tgl_terbit' => now(),
                'is_public' => $i % 2 == 0 ? 1 : 0,
            ]);
        }

        // ===== Buat Kegiatan Folder =====
        $kegiatanFolders = [];
        for ($i = 1; $i <= 4; $i++) {
            $folder = KegiatanFolder::create([
                'id' => Str::uuid(),
                'user_id' => $users[($i - 1) % count($users)]->id,
                'judul' => "Kegiatan $i - " . now()->subMonths($i)->monthName,
                'tanggal_kegiatan' => now()->subMonths($i)->startOfMonth(),
                'is_public' => $i % 2 == 0 ? 1 : 0,
                'slug' => Str::slug("Kegiatan $i"),
            ]);
            $kegiatanFolders[] = $folder;
        }

        // ===== Buat Kegiatan File =====
        foreach ($kegiatanFolders as $folder) {
            for ($i = 1; $i <= 3; $i++) {
                KegiatanFile::create([
                    'kegiatan_folder_id' => $folder->id,
                    'nama_file' => "File Kegiatan $i - " . $folder->judul . ".pdf",
                    'path' => "/storage/kegiatan/file$i.pdf",
                    'size' => rand(100, 5000),
                    'jumlah_download' => rand(0, 50),
                ]);
            }
        }

        // ===== Buat Sambutan =====
        $sambutanData = [
            'Sambutan Peluncuran Program Unggulan',
            'Sambutan Pembukaan Acara Penting',
            'Sambutan Penutupan Kegiatan',
        ];

        $sambutans = [];
        foreach ($sambutanData as $i => $judul) {
            $sambutan = NewSambutan::create([
                'user_id' => $users[$i % count($users)]->id,
                'judul' => $judul,
                'slug' => Str::slug($judul) . '-' . time() . $i,
                'deskripsi' => 'Deskripsi ' . $judul,
                'tanggal_terbit' => now(),
                'is_public' => 1,
            ]);
            $sambutans[] = $sambutan;
        }

        // ===== Buat Sambutan File =====
        foreach ($sambutans as $sambutan) {
            $types = ['naskah', 'tapping', 'presentasi', 'terjemahan'];
            foreach ($types as $j => $type) {
                SambutanFile::create([
                    'sambutan_id' => $sambutan->id,
                    'type' => $type,
                    'original_name' => "$type-{$sambutan->slug}.pdf",
                    'path' => "/storage/sambutan/$type-file.pdf",
                    'mime_type' => 'application/pdf',
                ]);
            }
        }

        // ===== Buat Post Social Media =====
        $platforms = ['Instagram Post', 'Instagram Reels', 'Youtube Video', 'Youtube Short', 'Tiktok', 'Facebook Post'];
        $posts = [
            'Post tentang kegiatan sosial bermanfaat',
            'Testimoni dari mitra terpercaya',
            'Update program unggulan kami',
            'Dokumentasi acara penting',
            'Berita terbaru dari tim kami',
            'Pengumuman jadwal kegiatan',
        ];

        foreach ($posts as $i => $judul) {
            PostSocialMedia::create([
                'id' => Str::uuid(),
                'user_id' => $users[$i % count($users)]->id,
                'option' => $platforms[$i % count($platforms)],
                'judul' => $judul,
                'link' => 'https://example.com/post-' . ($i + 1),
            ]);
        }
    }
}
