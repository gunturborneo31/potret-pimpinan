<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Permohonan extends Model
{
 use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

  protected $fillable = [
    'id', 'user_id', 'kategori', 'title', 'description',
    'priority', 'file', 'disposisi', 'status',
    'nama_pemohon', 'email_pemohon', 'no_hp_pemohon', 'instansi_pemohon'
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'disposisi');
    }

    public function komentars()
    {
        return $this->hasMany(Komentar::class);
    }

public function disposisi()
{
    return $this->belongsTo(User::class, 'disposisi', 'id');
}

}
