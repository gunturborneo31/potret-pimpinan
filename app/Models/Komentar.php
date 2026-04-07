<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Komentar extends Model
{
    use HasFactory;

 protected $fillable = [
    'permohonan_id',
    'user_id',
    'komentar',
    'file',
    'file_original_name',
];
    public function user() {
        return $this->belongsTo(User::class);
    }

    public function permohonan() {
        return $this->belongsTo(Permohonan::class);
    }
}
