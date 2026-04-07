<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class Sambutan extends Model
{
    use HasFactory;
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'judul',
        'tanggal_dibuat',
        'slug',
        'isi_sambutan',
        'file',
        'is_public',
        'user_id',
        'kontributor_id',
    ];

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    
   protected $casts = [
        'kontributor_id' => 'array', // pastikan ini ada
        'is_public' => 'boolean',
    ];



    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) \Str::uuid();
            }
        });
    }
 
    public function user() { return $this->belongsTo(User::class, 'user_id'); }

public function getKontributorAttribute()
    {
        if (!$this->kontributor_id || !is_array($this->kontributor_id)) {
            return collect();
        }

        return User::whereIn('id', $this->kontributor_id)->get();
    }

}
