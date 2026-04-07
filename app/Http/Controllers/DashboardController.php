<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Permohonan;
use App\Models\User;
use App\Models\Berita;
use App\Models\NewSambutan;
use App\Models\SambutanFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\KegiatanFile;
use App\Models\KegiatanFolder;
use App\Models\PostSocialMedia;
use Illuminate\Support\Facades\Schema; // ⬅️ tambahkan ini

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'BIASA') {
            return redirect()->route('permohonan.index');
        }

        // ===== Kegiatan: ringkasan utama =====
        $kegiatanTotal      = KegiatanFolder::count();
        $kegiatanFilesTotal = class_exists(\App\Models\KegiatanFile::class)
            ? KegiatanFile::count()
            : DB::table('kegiatan_files')->count();

        // Status publik / private
        $byStatus = KegiatanFolder::select('is_public', DB::raw('COUNT(*) as c'))
            ->groupBy('is_public')
            ->pluck('c', 'is_public');

        $kegiatanByStatus = [
            'public'  => (int) ($byStatus[1] ?? 0),
            'private' => (int) ($byStatus[0] ?? 0),
        ];

        // Total unduhan semua file
        $hasJumlahDownloadCol = Schema::hasColumn('kegiatan_files', 'jumlah_download');
        $kegiatanDownloadsTotal = $hasJumlahDownloadCol
            ? DB::table('kegiatan_files')->sum(DB::raw('COALESCE(jumlah_download,0)'))
            : 0;

        /**
         * ===== Pembuat & jumlah yang dibuat + jumlah_download per user =====
         * Deteksi nama FK di tabel kegiatan_files → join ke folders → group by user.
         */
        $kfTable  = 'kegiatan_files';
        $fkColumn = collect(['kegiatan_folder_id', 'folder_id', 'kegiatan_id'])
            ->first(fn ($c) => Schema::hasColumn($kfTable, $c));

        if ($fkColumn) {
            // Jika tahu FK, hitung total folder per user + SUM unduhan file-file di folder tsb
            $creatorRaw = KegiatanFolder::query()
                ->leftJoin("$kfTable as kf", "kf.$fkColumn", '=', 'kegiatan_folders.id')
                ->join('users as u', 'u.id', '=', 'kegiatan_folders.user_id')
                ->select(
                    'kegiatan_folders.user_id',
                    'u.name',
                    DB::raw('COUNT(DISTINCT kegiatan_folders.id) as total'),
                    // jika kolom tidak ada, COALESCE akan selalu 0 karena kf.jumlah_download null
                    DB::raw($hasJumlahDownloadCol
                        ? 'COALESCE(SUM(kf.jumlah_download),0) as jumlah_download'
                        : '0 as jumlah_download')
                )
                ->groupBy('kegiatan_folders.user_id', 'u.name')
                ->orderByDesc('total')
                ->get();
        } else {
            // Tidak tahu FK di kegiatan_files → fallback: kirim unduhan = 0 agar tidak error
            $creatorRaw = KegiatanFolder::query()
                ->join('users as u', 'u.id', '=', 'kegiatan_folders.user_id')
                ->select(
                    'kegiatan_folders.user_id',
                    'u.name',
                    DB::raw('COUNT(DISTINCT kegiatan_folders.id) as total'),
                    DB::raw('0 as jumlah_download')
                )
                ->groupBy('kegiatan_folders.user_id', 'u.name')
                ->orderByDesc('total')
                ->get();
        }

        $kegiatanCreators = $creatorRaw->map(fn($r) => [
            'user_id'         => $r->user_id,
            'name'            => $r->name ?? '—',
            'total'           => (int) $r->total,
            'jumlah_download' => (int) $r->jumlah_download, // ⬅️ inilah yang dipakai di tabel
        ])->values();

        $kegiatanCreatorUnique = $kegiatanCreators->count();

        // ===== Kegiatan: tren bulanan (12 bulan terakhir) =====
        $start = now()->startOfMonth()->subMonths(11);

        $monthly = KegiatanFolder::select(
                DB::raw("DATE_FORMAT(tanggal_kegiatan, '%Y-%m') as ym"),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(is_public = 1) as publik'),
                DB::raw('SUM(is_public = 0) as privat')
            )
            ->whereDate('tanggal_kegiatan', '>=', $start)
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        // Bentuk label 'MMM YYYY' (ID)
        $labels  = [];
        $total   = [];
        $publik  = [];
        $privat  = [];

        $cursor = $start->clone();
        for ($i = 0; $i < 12; $i++) {
            $ym = $cursor->format('Y-m');
            $row = $monthly->firstWhere('ym', $ym);
            $labels[] = $cursor->locale('id')->translatedFormat('MMM Y');
            $total[]  = (int) ($row->total  ?? 0);
            $publik[] = (int) ($row->publik ?? 0);
            $privat[] = (int) ($row->privat ?? 0);
            $cursor->addMonth();
        }

        $kegiatanMonthly = [
            'labels' => $labels,
            'total'  => $total,
            'publik' => $publik,
            'privat' => $privat,
        ];

        // ===== Sambutan =====
        $sambutanTotal = NewSambutan::count();

        $perUserRaw = NewSambutan::select('user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('user_id')
            ->with('user:id,name')
            ->get();

        $sambutanPerUser = $perUserRaw->map(function ($row) {
            return [
                'user_id' => $row->user_id,
                'name'    => optional($row->user)->name ?? '—',
                'total'   => (int) $row->total,
            ];
        })->sortByDesc('total')->values();

        $filesTotal = SambutanFile::count();

        $byType = SambutanFile::select('type', DB::raw('COUNT(*) as c'))
            ->groupBy('type')
            ->pluck('c', 'type');

        $types = ['naskah','tapping','presentasi','terjemahan'];
        $filesByType = collect($types)->mapWithKeys(
            fn ($t) => [$t => (int) ($byType[$t] ?? 0)]
        )->all();

        // ===== Berita =====
        $total   = Berita::count();
        $public  = Berita::where('is_public', 1)->count();
        $private = $total - $public;

        $penulisStats = User::query()
            ->select('users.id', 'users.name')
            ->join('beritas', 'beritas.penulis_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name')
            ->selectRaw('COUNT(beritas.id) AS jumlah')
            ->orderByDesc('jumlah')
            ->get();

        $lainnyaIdList = Berita::pluck('lainnya_id')
            ->filter()
            ->flatMap(function ($v) {
                if (is_array($v)) return $v;
                if (is_string($v)) return json_decode($v, true) ?: [];
                return [];
            })
            ->filter();

        $kontributorCounts = $lainnyaIdList->countBy()->sortDesc();

        $kontributorStats = User::whereIn('id', $kontributorCounts->keys())
            ->get(['id', 'name'])
            ->map(function ($u) use ($kontributorCounts) {
                return [
                    'id'     => (string) $u->id,
                    'name'   => $u->name,
                    'jumlah' => (int) ($kontributorCounts[(string) $u->id] ?? 0),
                ];
            })
            ->sortByDesc('jumlah')
            ->values();

        // ===== Sosial Media =====
        $sosmedTotal = PostSocialMedia::count();

        $sosmedByPlatform = PostSocialMedia::select('option', DB::raw('COUNT(*) as total'))
            ->groupBy('option')
            ->orderBy('option')
            ->pluck('total', 'option')
            ->toArray();

        $sosmedCreators = PostSocialMedia::query()
            ->join('users', 'users.id', '=', 'post_social_medias.user_id')
            ->select(
                'post_social_medias.user_id',
                'users.name',
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('post_social_medias.user_id', 'users.name')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'user_id' => $r->user_id,
                'name'    => $r->name,
                'total'   => (int) $r->total,
            ]);

        $sosmedCreatorUnique = PostSocialMedia::distinct('user_id')->count('user_id');

        return Inertia::render('Dashboard', [
            // Sosmed
            'sosmedTotal'         => $sosmedTotal,
            'sosmedByPlatform'    => $sosmedByPlatform,
            'sosmedCreators'      => $sosmedCreators,
            'sosmedCreatorUnique' => $sosmedCreatorUnique,

            // Kegiatan (termasuk total unduhan & unduhan per user)
            'kegiatanDownloadsTotal' => (int) $kegiatanDownloadsTotal,
            'kegiatanTotal'          => $kegiatanTotal,
            'kegiatanFilesTotal'     => $kegiatanFilesTotal,
            'kegiatanByStatus'       => $kegiatanByStatus,
            'kegiatanCreatorUnique'  => $kegiatanCreatorUnique,
            'kegiatanCreators'       => $kegiatanCreators,   // ⬅️ sudah berisi 'jumlah_download'
            'kegiatanMonthly'        => $kegiatanMonthly,

            // Sambutan
            'sambutanTotal'       => $sambutanTotal,
            'sambutanPerUser'     => $sambutanPerUser,
            'sambutanFilesTotal'  => $filesTotal,
            'sambutanFilesByType' => $filesByType,

            // Permohonan & Berita (eksisting)
            'beritaSummary' => [
                'total'   => $total,
                'public'  => $public,
                'private' => $private,
            ],
            'penulisStats'      => $penulisStats,
            'kontributorStats'  => $kontributorStats,
            'kontributorUnique' => $kontributorCounts->count(),
            'totalPermohonan' => Permohonan::count(),
            'kategoriCounts'  => Permohonan::selectRaw('kategori, count(*) as count')
                ->groupBy('kategori')
                ->pluck('count', 'kategori'),
            'statusCounts' => Permohonan::selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),
            'prioritasCounts' => Permohonan::selectRaw('priority, count(*) as count')
                ->groupBy('priority')
                ->pluck('count', 'priority'),
            'jumlahUserBiasa' => User::where('role', 'BIASA')->count(),
            'staffStats' => User::where('role', 'STAFF')->withCount([
                'disposisiPermohonan as total_disposisi',
                'disposisiPermohonan as diajukan_count' => fn($q) => $q->where('status', 'Diajukan'),
                'disposisiPermohonan as diproses_count' => fn($q) => $q->where('status', 'Diproses'),
                'disposisiPermohonan as selesai_count' => fn($q) => $q->where('status', 'Selesai'),
                'disposisiPermohonan as ditolak_count' => fn($q) => $q->where('status', 'Ditolak'),
            ])->get(),
        ]);
    }
}
