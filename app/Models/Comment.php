<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    /**

     * The attributes that are mass assignable.

     *

     * @var array

     */

    protected $fillable = ['user_id', 'post_id', 'comment'];



    /**
 
     * The belongs to Relationship
 
     *
 
     * @var array
 
     */

    public function user()

    {

        return $this->belongsTo(User::class);
    }
}
