<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function sendPasswordResetNotification($token)
    {
        $url = 'http://localhost:8000/api/reset-password?token=' . $token;

        $this->notify(new ResetPasswordNotification($url));
    }

    public function post()
    {
        return $this->hasMany(Post::class)->select("title", "desc", "public");
    }

    public function comment()
    {
        return $this->hasMany(Comment::class)->select("post_id", "comment");
    }

    public function rating()
    {
        return $this->hasMany(Rating::class)->select("rating");
    }

    public function shares()
    {
        return $this->hasMany(Share::class);
    }
}
