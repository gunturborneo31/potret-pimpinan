<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostSocialMedia extends Model
{
    use HasFactory;
    protected $table = 'post_social_medias';

    protected $fillable = ['id', 'judul', 'link', 'option', 'user_id'];
    public $incrementing = false;
    protected $keyType = 'string';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
