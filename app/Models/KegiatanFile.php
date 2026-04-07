<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegiatanFile extends Model
{
    protected $fillable = ['kegiatan_folder_id', 'nama_file', 'path', 'checked', 'size' ,'jumlah_download'];

    protected $casts = [
    'checked' => 'boolean',
];

   public function folder()
    {
        return $this->belongsTo(KegiatanFolder::class, 'kegiatan_folder_id');
    }

    
}