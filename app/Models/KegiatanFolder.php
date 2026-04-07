<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KegiatanFolder extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'judul', 'tanggal_kegiatan', 'pejabat_hadir', 'parent_id', 'slug', 'is_public', 'slug_secret', 'is_secret', 'is_favorite', 'user_id'];

    protected $casts = [
    'is_public' => 'boolean',
    'is_favorite' => 'boolean',
];

    public function subfolders()
    {
        return $this->hasMany(KegiatanFolder::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(KegiatanFolder::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(KegiatanFolder::class, 'parent_id');
    }

    public function files()
    {
        return $this->hasMany(KegiatanFile::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function getRouteKeyName()
    {
        return 'slug';
    }
    
    public function favorites()
{
    return $this->hasMany(KegiatanFavorite::class);
}

}

