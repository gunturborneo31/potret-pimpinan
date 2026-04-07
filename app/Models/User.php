<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    public $incrementing = false;
    protected $keyType = 'string';

protected static function boot()
{
    parent::boot();

    static::creating(function ($model) {
        $model->id = (string) Str::uuid();
    });
}
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'no_hp',
        'email',
        'password',
        'plain_text',
        'role',
        'tipe',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

public function getTipeAttribute()
{
    return $this->attributes['tipe'];
}


    public function disposisiPermohonan()
{
    return $this->hasMany(Permohonan::class, 'disposisi');
}

    public function penulis() { 
        return $this->belongsTo(User::class, 'penulis_id');
    }

}
