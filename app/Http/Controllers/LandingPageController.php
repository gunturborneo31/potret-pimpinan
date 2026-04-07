<?php

namespace App\Http\Controllers;

use App\Models\KegiatanFolder;
use App\Models\KegiatanFile;
use App\Models\Permohonan;
use App\Models\Berita;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;

class LandingPageController extends Controller
{
    public function index()
    {
       $permohonanTerbaru = Permohonan::with('user')->latest()->take(4)->get();
    $folderPublik = KegiatanFolder::with('files')->where('is_public', true)->latest()->take(5)->get();
         $beritaTerbaru = Berita::with(['penulis:id,name'])
            ->where('is_public', true)
            ->orderBy('tanggal_terbit','desc')
            ->take(4)
            ->get()
            ->map(function ($b) {
                return [
                    'id'            => $b->id,
                    'judul'         => $b->judul,
                    'slug'          => $b->slug,
                    'tanggal_terbit'=> optional($b->tanggal_terbit)->toDateString(),
                    'penulis_nama'  => optional($b->penulis)->name,
                    'thumbnail_url' => $b->thumbnail_url,
                    'jumlah_view'   => (int) $b->jumlah_view,
                ];
            });
            // return $beritaTerbaru;

    return Inertia::render('Landing/Index', [
        'permohonanTerbaru' => $permohonanTerbaru,
        'beritaTerbaru' => $beritaTerbaru,
        'folderPublik' => $folderPublik,
            'statistikPermohonan' => [
                'total' => Permohonan::count(),
                'sambutan' => Permohonan::where('kategori', 'Sambutan')->count(),
                'dokumentasi' => Permohonan::where('kategori', 'Dokumentasi')->count(),
                'lainnya' => Permohonan::where('kategori', 'Lainnya')->count(),
            ],
        'statistik' => [
            'totalFolder' => KegiatanFolder::count(),
            'totalUser' => User::where('role', 'BIASA')->count(),
            'totalFile' => KegiatanFile::count(),
            'totalDownload' => KegiatanFile::sum('jumlah_download'),
        ],
        'feedbackStats' => [ // ✅ Tambahan
      'happy' => Feedback::where('emote', 'senyum-lebar')->count(),
      'neutral' => Feedback::where('emote', 'senyum-tipis')->count(),
      'sad' => Feedback::where('emote', 'merengut')->count(),
  ],
        
    ]);

    }
    
   public function beritadetail(Request $request, string $slug)
{
    $berita = Berita::with(['penulis:id,name'])
        ->where('slug', $slug)
        ->where('is_public', true)
        ->firstOrFail();

    // Tambah views berbasis session (1x per sesi per berita)
    $key = 'viewed_berita_' . $berita->id;
    if (!$request->session()->has($key)) {
        $berita->increment('jumlah_view');
        $request->session()->put($key, now()->toIso8601String());
    }

    return Inertia::render('Landing/DetailBerita', [
        'berita' => [
            'id'             => $berita->id,
            'judul'          => $berita->judul,
            'slug'           => $berita->slug,
            'isi_berita'     => $berita->isi_berita,
            // jika model sudah di-cast date, ini aman:
            'tanggal_terbit'=> optional($berita->tanggal_terbit)->toDateString(),

            'penulis_nama'   => optional($berita->penulis)->name,
            // pakai accessor getThumbnailUrlAttribute() kalau kamu punya
            'thumbnail_url'  => method_exists($berita, 'getThumbnailUrlAttribute')
                ? $berita->thumbnail_url
                : ($berita->thumbnail
                    ? (str_starts_with($berita->thumbnail, 'http')
                        ? $berita->thumbnail
                        : asset('storage/' . $berita->thumbnail))
                    : asset('img/placeholder-news.jpg')),
            'jumlah_view'    => (int) $berita->jumlah_view,
        ],
    ]);
}

public function showPublic($slug)
{
    $folder = \App\Models\KegiatanFolder::where('slug', $slug)
        ->where('is_public', 1)
        ->firstOrFail();
    

    // ✅ Ambil SEMUA file, urutkan: checked=1 dulu, lalu created_at terbaru
    $files = $folder->files()
        ->orderByDesc('checked')   // checked = 1 muncul duluan
        ->latest()                 // lalu urutkan per created_at desc
        ->get();

            $filessatu = $folder->files()
        ->where('checked', '1')   // checked = 1 muncul duluan
        ->get();
        

    // (opsional) tetap kirim info jumlah yang belum checked
    $jumlah_unchecked = $folder->files()->where('checked', 0)->count();

    return inertia('Landing/ShowPublic', [
        'folder'            => $folder,
        'files'             => $files,
        'filessatu'             => $filessatu,
        'jumlah_unchecked'  => $jumlah_unchecked,
    ]);
}

public function watermarkPublic(\App\Models\KegiatanFile $file)
{
    @ini_set('memory_limit', '1024M');

    $disk   = 'public';
    $source = $file->path;

    // Normalisasi jika ada "storage/..."
    if ($source && str_starts_with($source, 'storage/')) {
        $source = substr($source, strlen('storage/'));
    }

    if (!$source || !Storage::disk($disk)->exists($source)) {
        return $this->lockedPlaceholder('File tidak ditemukan');
    }

    $mime = Storage::disk($disk)->mimeType($source) ?? '';
    $ext  = strtolower(pathinfo($source, PATHINFO_EXTENSION));
    $imageExts = ['jpg','jpeg','png','gif','webp','heic','heif'];
    if (!(str_starts_with($mime, 'image/') || in_array($ext, $imageExts, true))) {
        return $this->lockedPlaceholder('Bukan gambar');
    }

    // ====== Pengaturan Watermark (tiled kecil) ======
    $wmPath  = public_path('img/watermark.png');       // watermark PNG transparan
    $scale   = (float) request()->query('wm', 0.18);   // default lebih kecil; 0.05–0.50
    $opacity = (float) request()->query('op', 0.25);   // 0–1
    $angle   = (float) request()->query('angle', 30);  // derajat
    $gap     = (int)   request()->query('gap', 0);     // px; 0 = auto (berdasar ukuran wm)
    $tile    = (int)   request()->query('tile', 1);    // 1 = sebarkan (tile)

    // clamp agar aman
    $scale   = max(0.05, min(0.50, $scale));
    $opacity = max(0.00, min(1.00, $opacity));

    // Cache key memasukkan parameter agar variasi tidak saling timpa
    $sig = 't'.$tile.'_s'.(int)round($scale*100).'_o'.(int)round($opacity*100).'_a'.(int)$angle.'_g'.max(0,$gap);

    $previewDisk = 'public';
    $outBase     = "previews/{$file->id}_wmTiledSmall_{$sig}";
    $outPath     = Storage::disk($previewDisk)->exists("{$outBase}.webp") ? "{$outBase}.webp" : "{$outBase}.jpg";

    // Rebuild jika sumber berubah
    $rebuild = true;
    if (Storage::disk($previewDisk)->exists($outPath)) {
        $srcM = Storage::disk($disk)->lastModified($source);
        $dstM = Storage::disk($previewDisk)->lastModified($outPath);
        $rebuild = $dstM < $srcM;
    }

    if ($rebuild) {
        $fullPath = Storage::disk($disk)->path($source);

        // ========== Cabang 1: Imagick ==========
        if (extension_loaded('imagick')) {
            try {
                $im = new \Imagick();
                $im->readImage($fullPath);

                // Ambil frame pertama jika animasi
                if ($im->getNumberImages() > 1) {
                    $im = $im->coalesceImages();
                    $im->nextImage();
                    $im->setIteratorIndex(0);
                }

                // Auto-orient
                if (method_exists($im, 'autoOrient')) {
                    try { $im->autoOrient(); } catch (\Throwable $e) {}
                } else {
                    try { $im->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT); } catch (\Throwable $e) {}
                }

                // Resize hemat
                $w = $im->getImageWidth(); $h = $im->getImageHeight();
                if ($w > 1600 || $h > 1600) {
                    $im->thumbnailImage(1600, 1600, true, true);
                    $w = $im->getImageWidth(); $h = $im->getImageHeight();
                }

                if (is_file($wmPath)) {
                    // ---- Watermark gambar (PNG transparan) → TILE kecil ----
                    $wm = new \Imagick($wmPath);
                    $wm->setImageAlphaChannel(\Imagick::ALPHACHANNEL_ACTIVATE);

                    // skala watermark relatif lebar foto (kecil)
                    $targetW = (int) round($w * $scale);
                    if ($targetW > 0) {
                        $wm->resizeImage($targetW, 0, \Imagick::FILTER_LANCZOS, 1, true);
                    }

                    // rotasi
                    if ((int)$angle !== 0) {
                        $wm->rotateImage(new \ImagickPixel('transparent'), $angle);
                    }

                    // atur opacity
                    try {
                        $wm->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $opacity, \Imagick::CHANNEL_ALPHA);
                    } catch (\Throwable $e) {
                        if (method_exists($wm, 'setImageOpacity')) {
                            $wm->setImageOpacity($opacity);
                        }
                    }

                    $wmW = $wm->getImageWidth();
                    $wmH = $wm->getImageHeight();

                    if ($tile) {
                        // langkah grid (default: 25% dari ukuran wm)
                        $stepX = $wmW + ($gap > 0 ? $gap : (int)round($wmW * 0.25));
                        $stepY = $wmH + ($gap > 0 ? $gap : (int)round($wmH * 0.25));

                        for ($yy = -$wmH; $yy < $h + $wmH; $yy += $stepY) {
                            for ($xx = -$wmW; $xx < $w + $wmW; $xx += $stepX) {
                                $im->compositeImage($wm, \Imagick::COMPOSITE_OVER, (int)$xx, (int)$yy);
                            }
                        }
                    } else {
                        $x = (int) max(0, ($w - $wmW) / 2);
                        $y = (int) max(0, ($h - $wmH) / 2);
                        $im->compositeImage($wm, \Imagick::COMPOSITE_OVER, $x, $y);
                    }

                    $wm->clear(); $wm->destroy();
                } else {
                    // ---- Fallback Teks: TILE juga (kecil) ----
                    $text  = 'Biro Adpim - Setda Prov. Kaltim';
                    $draw  = new \ImagickDraw();
                    $draw->setFillColor(new \ImagickPixel("rgba(255,255,255,".(string)$opacity.")"));
                    $draw->setStrokeColor(new \ImagickPixel("rgba(0,0,0,".(string)min(1.0, $opacity*0.6).")"));
                    $draw->setStrokeWidth(1);
                    $draw->setFontSize(max((int) round(min($w, $h) * 0.06), 18)); // sedikit lebih kecil
                    $draw->setGravity(\Imagick::GRAVITY_NORTHWEST);
                    // $draw->setFont(resource_path('fonts/Inter-ExtraBold.ttf')); // opsional

                    $metrics = $im->queryFontMetrics($draw, $text);
                    $tW = (int) ceil($metrics['textWidth']);
                    $tH = (int) ceil($metrics['textHeight']);
                    $stepX = $tW + ($gap > 0 ? $gap : (int)round($tW * 0.35));
                    $stepY = $tH + ($gap > 0 ? $gap : (int)round($tH * 0.60));

                    $tileLayer = new \Imagick();
                    $tileLayer->newImage($w, $h, new \ImagickPixel('transparent'));
                    $tileLayer->setImageFormat('png');

                    for ($yy = -$tH; $yy < $h + $tH; $yy += $stepY) {
                        for ($xx = -$tW; $xx < $w + $tW; $xx += $stepX) {
                            $tileLayer->annotateImage($draw, (int)$xx, (int)$yy, $angle, $text);
                        }
                    }

                    $im->compositeImage($tileLayer, \Imagick::COMPOSITE_OVER, 0, 0);
                    $tileLayer->clear(); $tileLayer->destroy();
                }

                // Encode WEBP → fallback JPEG
                try {
                    $im->setImageFormat('webp');
                    $im->setImageCompressionQuality(60);
                    $blob   = $im->getImagesBlob();
                    $outPath = "{$outBase}.webp";
                    Storage::disk($previewDisk)->put($outPath, $blob);
                } catch (\Throwable $e) {
                    $im->setImageFormat('jpeg');
                    $im->setImageCompressionQuality(70);
                    $blob   = $im->getImagesBlob();
                    $outPath = "{$outBase}.jpg";
                    Storage::disk($previewDisk)->put($outPath, $blob);
                }

                $im->clear(); $im->destroy();
            } catch (\Throwable $e) {
                \Log::notice('WM Imagick gagal, fallback GD', [
                    'file_id' => $file->id,
                    'path'    => $source,
                    'mime'    => $mime,
                    'ext'     => $ext,
                    'msg'     => $e->getMessage(),
                ]);
                // lanjut ke GD
            }
        }

        // Jika sudah jadi di Imagick, kirim
        if (Storage::disk($previewDisk)->exists($outPath)) {
            return response()->file(
                Storage::disk($previewDisk)->path($outPath),
                ['Cache-Control' => 'public, max-age=604800']
            );
        }

        // ========== Cabang 2: GD (fallback) ==========
        if (in_array($ext, ['heic','heif'], true)) {
            return $this->lockedPlaceholder('Format HEIC/HEIF tidak didukung server');
        }

        try {
            $data = @file_get_contents($fullPath);
            if ($data === false) {
                return $this->lockedPlaceholder('Tidak bisa membaca file');
            }
            $img = @imagecreatefromstring($data);
            if (!$img) {
                return $this->lockedPlaceholder('Gambar tidak valid/korup');
            }

            // Resize ke max 1600
            $w = imagesx($img); $h = imagesy($img);
            $scaleDown = min(1600 / max($w,1), 1600 / max($h,1), 1);
            if ($scaleDown < 1) {
                $newW = (int) floor($w * $scaleDown);
                $newH = (int) floor($h * $scaleDown);
                $dst  = imagecreatetruecolor($newW, $newH);
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
                imagecopyresampled($dst, $img, 0, 0, 0, 0, $newW, $newH, $w, $h);
                imagedestroy($img);
                $img = $dst;
                $w = $newW; $h = $newH;
            }

            imagealphablending($img, true);
            imagesavealpha($img, true);

            if (is_file($wmPath)) {
                // ---- Watermark gambar (PNG transparan) → TILE kecil ----
                $wm = @imagecreatefrompng($wmPath);
                if ($wm) {
                    imagealphablending($wm, true);
                    imagesavealpha($wm, true);

                    $wmW = imagesx($wm); $wmH = imagesy($wm);
                    $targetW = (int) round($w * $scale); // kecil

                    if ($targetW > 0 && $wmW > 0) {
                        $ratio = $wmH / max($wmW,1);
                        $newW  = $targetW;
                        $newH  = (int) round($targetW * $ratio);
                        $wmRes = imagecreatetruecolor($newW, $newH);
                        imagealphablending($wmRes, false);
                        imagesavealpha($wmRes, true);
                        imagecopyresampled($wmRes, $wm, 0, 0, 0, 0, $newW, $newH, $wmW, $wmH);
                        imagedestroy($wm);
                        $wm = $wmRes;
                        $wmW = $newW; $wmH = $newH;
                    }

                    if ((int)$angle !== 0) {
                        $bg  = imagecolorallocatealpha($wm, 0, 0, 0, 127);
                        $rot = imagerotate($wm, -$angle, $bg); // arah GD kebalikan
                        if ($rot) {
                            imagesavealpha($rot, true);
                            imagedestroy($wm);
                            $wm = $rot;
                            $wmW = imagesx($wm); $wmH = imagesy($wm);
                        }
                    }

                    $stepX = $wmW + ($gap > 0 ? $gap : (int)round($wmW * 0.25));
                    $stepY = $wmH + ($gap > 0 ? $gap : (int)round($wmH * 0.25));

                    if ($tile) {
                        for ($yy = -$wmH; $yy < $h + $wmH; $yy += $stepY) {
                            for ($xx = -$wmW; $xx < $w + $wmW; $xx += $stepX) {
                                imagecopy($img, $wm, (int)$xx, (int)$yy, 0, 0, $wmW, $wmH);
                            }
                        }
                    } else {
                        $x = (int) max(0, ($w - $wmW) / 2);
                        $y = (int) max(0, ($h - $wmH) / 2);
                        imagecopy($img, $wm, $x, $y, 0, 0, $wmW, $wmH);
                    }

                    imagedestroy($wm);
                } else {
                    // PNG gagal → teks tiled kecil
                    $this->gdTileTextSmall($img, $w, $h, $angle, $gap);
                }
            } else {
                // Tidak ada PNG → teks tiled kecil
                $this->gdTileTextSmall($img, $w, $h, $angle, $gap);
            }

            // Simpan WEBP → fallback JPEG
            ob_start();
            if (function_exists('imagewebp')) {
                imagewebp($img, null, 60);
                $blob   = ob_get_clean();
                $outPath = "{$outBase}.webp";
            } else {
                imagejpeg($img, null, 70);
                $blob   = ob_get_clean();
                $outPath = "{$outBase}.jpg";
            }
            imagedestroy($img);
            Storage::disk($previewDisk)->put($outPath, $blob);
        } catch (\Throwable $e) {
            return $this->lockedPlaceholder('Gagal memproses watermark (GD)', $e->getMessage());
        }
    }

    return response()->file(
        Storage::disk($previewDisk)->path($outPath),
        ['Cache-Control' => 'public, max-age=604800']
    );
}

/**
 * Helper: Tiling watermark teks kecil (fallback GD)
 */
private function gdTileTextSmall($img, int $w, int $h, float $angle, int $gap): void
{
    $white = imagecolorallocatealpha($img, 255, 255, 255, 127 - (int)(0.80*127));
    $black = imagecolorallocatealpha($img, 0, 0, 0,   127 - (int)(0.35*127));

    $text = 'Biro Adpim - Setda Prov. Kaltim';
    $fontPath = is_file(resource_path('fonts/Inter-ExtraBold.ttf'))
        ? resource_path('fonts/Inter-ExtraBold.ttf')
        : null;

    if ($fontPath && function_exists('imagettfbbox') && function_exists('imagettftext')) {
        $fs = max((int) round(min($w, $h) * 0.05), 16); // font size kecil
        $bbox = imagettfbbox($fs, 0, $fontPath, $text);
        $tW   = abs($bbox[2] - $bbox[0]);
        $tH   = abs($bbox[7] - $bbox[1]);

        $stepX = $tW + ($gap > 0 ? $gap : (int)round($tW * 0.35));
        $stepY = $tH + ($gap > 0 ? $gap : (int)round($tH * 0.60));

        for ($yy = -$tH; $yy < $h + $tH; $yy += $stepY) {
            for ($xx = -$tW; $xx < $w + $tW; $xx += $stepX) {
                // Shadow kecil
                imagettftext($img, $fs, $angle, (int)$xx+1, (int)$yy+1, $black, $fontPath, $text);
                // Main
                imagettftext($img, $fs, $angle, (int)$xx,   (int)$yy,   $white, $fontPath, $text);
            }
        }
    } else {
        // Fallback tanpa TTF
        $charW = 6; $charH = 12;
        $tW = $charW * strlen($text);
        $tH = $charH;
        $stepX = $tW + ($gap > 0 ? $gap : (int)round($tW * 0.35));
        $stepY = $tH + ($gap > 0 ? $gap : (int)round($tH * 0.60));

        for ($yy = -$tH; $yy < $h + $tH; $yy += $stepY) {
            for ($xx = -$tW; $xx < $w + $tW; $xx += $stepX) {
                imagestring($img, 2, (int)$xx, (int)$yy, $text, $white); // font GD kecil
            }
        }
    }
}



private function lockedPlaceholder(string $reason = 'Pratinjau tidak tersedia', ?string $detail = null)
{
    $locked = public_path('img/file-locked.png');
    if (is_file($locked)) {
        return response()->file($locked, ['Cache-Control' => 'public, max-age=604800']);
    }
    $reason = htmlspecialchars($reason, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $detail = htmlspecialchars((string) $detail, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $svg = <<<SVG
<?xml version="1.0" encoding="UTF-8"?>
<svg width="1200" height="800" viewBox="0 0 1200 800" xmlns="http://www.w3.org/2000/svg">
  <rect width="1200" height="800" fill="#2d2d2d"/>
  <text x="600" y="260" font-size="90" text-anchor="middle">🔒</text>
  <text x="600" y="360" fill="#ffffff" fill-opacity="0.9" font-size="36" font-weight="700" text-anchor="middle">{$reason}</text>
  <text x="600" y="410" fill="#ffffff" fill-opacity="0.75" font-size="22" text-anchor="middle">{$detail}</text>
  <text x="600" y="700" fill="#ffffff" fill-opacity="0.22" font-size="32" font-weight="800" text-anchor="middle" transform="rotate(-10 600 700)">
    Biro Adpim - Seetda Prov. Kaltim
  </text>
</svg>
SVG;
    return response($svg, 200, ['Content-Type' => 'image/svg+xml; charset=utf-8']);
}


public function listBerita(Request $request)
{
    $search  = $request->input('search');
    $tahun   = $request->input('tahun');
    $bulan   = $request->input('bulan');
    $tanggal = $request->input('tanggal');

    $query = Berita::with('penulis:id,name')
        ->where('is_public', 1)
        ->when($search, function ($q, $search) {
            $q->where('judul', 'like', "%{$search}%");
        })
        ->when($tahun, function ($q, $tahun) {
            $q->whereYear('tanggal_terbit', $tahun);
        })
        ->when($bulan, function ($q, $bulan) {
            $q->whereMonth('tanggal_terbit', $bulan);
        })
        ->when($tanggal, function ($q, $tanggal) {
            $q->whereDate('tanggal_terbit', $tanggal);
        })
        ->latest('tanggal_terbit');

    // Sama seperti listKegiatan: paginate(100) + withQueryString()
    // Sekaligus transform item agar punya field yang dibutuhkan frontend.
    $berita = $query->paginate(50)->withQueryString()
        ->through(function ($b) {
            return [
                'id'             => $b->id,
                'judul'          => $b->judul,
                'slug'           => $b->slug,
               'tanggal_terbit' => $b->tanggal_terbit
    ? Carbon::parse($b->tanggal_terbit)->toDateString()
    : null,
                'penulis_nama'   => optional($b->penulis)->name,
                'thumbnail_url'  => $b->thumbnail && str_starts_with($b->thumbnail, 'http')
                    ? $b->thumbnail
                    : ($b->thumbnail ? asset('storage/'.$b->thumbnail) : asset('img/thumb.jpg')),
                'jumlah_view'    => (int) $b->jumlah_view,
            ];
        });

    return Inertia::render('Landing/ListBerita', [
        'berita'  => $berita,
        'filters' => $request->only(['search', 'tahun', 'bulan', 'tanggal']),
    ]);
}

public function kegiatanPublik(Request $request)
{
    $search        = $request->input('search');
    $tahun         = $request->input('tahun');
    $bulan         = $request->input('bulan');
    $tanggal       = $request->input('tanggal');
    $pejabatHadir  = $request->input('pejabat_hadir');

    $folders = KegiatanFolder::with('files')
        ->where('is_public', 1)
        ->when($search, function ($query, $search) {
            $query->where('judul', 'like', "%{$search}%");
        })
        ->when($tahun, function ($query, $tahun) {
            $query->whereYear('tanggal_kegiatan', $tahun);
        })
        ->when($bulan, function ($query, $bulan) {
            $query->whereMonth('tanggal_kegiatan', $bulan);
        })
        ->when($tanggal, function ($query, $tanggal) {
            $query->whereDate('tanggal_kegiatan', $tanggal);
        })
        ->when($pejabatHadir, function ($query, $pejabatHadir) {
            $query->where('pejabat_hadir', $pejabatHadir);
        })
        ->latest()
        ->paginate(100)
        ->withQueryString();

    return Inertia::render('Landing/ListKegiatanPublic', [
        'folders' => $folders,
        'filters' => $request->only(['search','tahun','bulan','tanggal','pejabat_hadir']),
    ]);
}


public function downloadZip($slug)
{
    $folder = KegiatanFolder::where('slug', $slug)
        ->where('is_public', true)
        ->with(['files' => function ($q) {
            $q->where('checked', 1);
        }])
        ->firstOrFail();

    if ($folder->files->isEmpty()) {
        return back()->with('error', 'Tidak ada file untuk diunduh.');
    }


    // Tambah jumlah download untuk setiap file
    foreach ($folder->files as $file) {
        $file->increment('jumlah_download');
    }

    // ZIP setup
    $zipFileName = Str::slug($folder->judul) . '-' . now()->timestamp . '.zip';
    $zipPath = storage_path("app/public/zips/{$zipFileName}");

    if (!Storage::disk('public')->exists('zips')) {
        Storage::disk('public')->makeDirectory('zips');
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return back()->with('error', 'Gagal membuat ZIP.');
    }

    foreach ($folder->files as $file) {
        $fullPath = storage_path("app/public/{$file->path}");
        if (file_exists($fullPath)) {
            $zip->addFile($fullPath, $file->nama_file);
        }
    }

    $zip->close();

    return response()->download($zipPath)->deleteFileAfterSend(true);
}

public function secretdownloadZip($slug)
{
$folder = KegiatanFolder::where('slug_secret', $slug)
        ->where('is_public', false)
        ->with('files') // <-- tanpa where checked
        ->firstOrFail();

    if ($folder->files->isEmpty()) {
        return back()->with('error', 'Tidak ada file untuk diunduh.');
    }

    // Tambah jumlah download untuk setiap file
    foreach ($folder->files as $file) {
        $file->increment('jumlah_download');
    }

    // ZIP setup
    $zipFileName = Str::slug($folder->judul) . '-' . now()->timestamp . '.zip';
    $zipPath = storage_path("app/public/zips/{$zipFileName}");

    if (!Storage::disk('public')->exists('zips')) {
        Storage::disk('public')->makeDirectory('zips');
    }

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return back()->with('error', 'Gagal membuat ZIP.');
    }

    foreach ($folder->files as $file) {
        $fullPath = storage_path("app/public/{$file->path}");
        if (file_exists($fullPath)) {
            // Nama file di dalam zip = nama_file asli
            $zip->addFile($fullPath, $file->nama_file);
        }
    }

    $zip->close();

    return response()->download($zipPath)->deleteFileAfterSend(true);
}


public function incrementDownload($id)
{
    $file = KegiatanFile::findOrFail($id);
    $file->increment('jumlah_download');
    return response()->json(['success' => true, 'jumlah_download' => $file->jumlah_download]);
}

public function downloadFile($id)
{
    $file = KegiatanFile::findOrFail($id);

    // Tambah jumlah download
    $file->increment('jumlah_download');

    // Download file
    return Storage::disk('public')->download($file->path, $file->nama_file);
}


}

