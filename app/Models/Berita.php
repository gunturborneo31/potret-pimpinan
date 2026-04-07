<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;

class Berita extends Model
{
   use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;

  protected $fillable = [
    'judul',
    'slug',
    'isi_berita',
    'tanggal_terbit',
    'penulis_id',
    'editor_id',
    'lainnya_id',
    'kegiatan_id',
    'thumbnail',
    'file',
    'is_public',
    'user_id', // <== ini wajib
];

   protected $casts = [
        'lainnya_id' => 'array', // pastikan ini ada
        'is_public' => 'boolean',
        'tanggal_terbit' => 'date',
    ];

    public function getRouteKeyName()
{
    return 'uuid';
}

    protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        if (empty($model->{$model->getKeyName()})) {
            $model->{$model->getKeyName()} = (string) \Str::uuid();
        }
    });
}
    public function penulis() { return $this->belongsTo(User::class, 'penulis_id'); }
    public function editor() { return $this->belongsTo(User::class, 'editor_id'); }
    public function lainnya() { return $this->belongsTo(User::class, 'lainnya_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id'); }
    public function kegiatan() { return $this->belongsTo(KegiatanFolder::class, 'kegiatan_id'); }
    public function usersLainnya()
{
    return $this->belongsToMany(User::class, 'berita_user', 'berita_id', 'user_id');
}

public function getLainnyaAttribute()
    {
        if (!$this->lainnya_id || !is_array($this->lainnya_id)) {
            return collect();
        }

        return User::whereIn('id', $this->lainnya_id)->get();
    }

     // Helper URL thumbnail (pakai storage)
    public function getThumbnailUrlAttribute()
    {
        if (!$this->thumbnail) return asset('img/thumb.jpg'); // siapkan gambar fallback
        // jika thumbnail sudah full URL, langsung kembalikan
        if (str_starts_with($this->thumbnail, 'http')) return $this->thumbnail;
        return asset('storage/'.$this->thumbnail);
    }
}
