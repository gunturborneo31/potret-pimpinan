<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewSambutan extends Model
{
    protected $table = 'newsambutans';

    protected $fillable = ['judul','slug','tanggal_terbit','deskripsi','is_public','user_id'];

    protected $casts = [
        'tanggal_terbit' => 'date',
        'is_public' => 'boolean',
    ];

    // ⬇️ UUID settings
    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
            if (empty($model->slug) && !empty($model->judul)) {
                $base = Str::slug($model->judul);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$i++;
                }
                $model->slug = $slug;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function files() {
        return $this->hasMany(SambutanFile::class);
    }

    public function filesByType(string $type) {
        return $this->files()->where('type', $type);
    }
}
