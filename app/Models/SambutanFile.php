<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SambutanFile extends Model
{
    protected $fillable = [
        'sambutan_id','type','path','original_name','size','mime_type','uploaded_by'
    ];
    
    protected $appends = ['url'];

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
        });
    }

    public function sambutan() {
        return $this->belongsTo(Sambutan::class);
    }

    public function getUrlAttribute() {
        if (str_starts_with($this->path, 'http')) return $this->path;
        return asset('storage/'.$this->path);
    }
    
}
