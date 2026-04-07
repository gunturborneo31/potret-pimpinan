<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Folder extends Model
{

protected static function boot()
{
    parent::boot();
    static::creating(function ($model) {
        $model->id = (string) Str::uuid();
    });
}

}
