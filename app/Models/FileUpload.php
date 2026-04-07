<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FileUpload extends Model
{
    protected $fillable = [
    'folder_id',
    'file_path',
    'original_name',
    'file_type',
    'file_size',
];


protected static function boot()
{
    parent::boot();
    static::creating(function ($model) {
        $model->id = (string) Str::uuid();
    });
}

}
