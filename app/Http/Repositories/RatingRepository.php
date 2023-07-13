<?php

namespace App\Http\Repositories;

use App\Models\Post;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;

class RatingRepository
{
    public function index($postId)
    {
        $post = Post::find($postId);

        if (!$post) {
            throw new \Exception('Ratings not found', 404);
        }

        if ($post->public === 0 && $post->user_id !== Auth::user()->id) {
            throw new \Exception('You do not have access to this post', 404);
        }

        $ratings = $post->ratings;

        return $ratings;
    }

    public function show($id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            throw new \Exception('Rating not found', 404);
        }

        return $rating;
    }

    public function upsert($postId = null, array $data)
    {
        $userId = Auth::user()->id;

        $post = Post::find($postId);

        if ($post->public === 0 && !$post->shares()->where('user_id', $userId)->exists() && $post->user_id !== $userId) {
            throw new \Exception('You are not authorized to make this request', 403);
        }

        $existingRating = Rating::where('user_id', $userId)
            ->where('post_id', $postId)
            ->first();

        if ($existingRating) {
            $existingRating->rating = $data['rating'];
            $existingRating->save();

            return $existingRating;
        }

        $rating = Rating::create([
            'user_id' => $userId,
            'post_id' => $postId,
            'rating' => $data['rating'],
        ]);

        return $rating;
    }

    public function destroy($id)
    {
        $rating = Rating::find($id);

        if (!$rating) {
            throw new \Exception('Comment not found', 404);
        }

        $rating->delete();
    }
}
