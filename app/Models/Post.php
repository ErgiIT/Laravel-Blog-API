<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id", "title", "desc", "public"
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()

    {
        return $this->hasMany(Comment::class)->select("user_id", "comment");
    }

    public function ratings()

    {
        return $this->hasMany(Rating::class)->select("user_id", "rating");
    }

    public function shares()

    {
        return $this->hasMany(Share::class);
    }
}
