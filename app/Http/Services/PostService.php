<?php

namespace App\Http\Services;

use App\Models\Post;
use App\Http\Resources\PostsResource;
use Illuminate\Support\Facades\Auth;

class PostService
{
    public function getSimilarAndRankedPosts($postId)
    {
        $loggedInUserId = Auth::user()->id;

        $post = Post::where('id', $postId)
            ->where(function ($query) use ($loggedInUserId) {
                $query->where('user_id', $loggedInUserId)
                    ->orWhere("public", true)
                    ->orWhereHas('shares', function ($query) use ($loggedInUserId) {
                        $query->where('user_id', $loggedInUserId);
                    });
            })
            ->first();

        if ($post) {
            $categoryNames = $post->categories->pluck('name');

            $similarPosts = Post::where(function ($query) use ($categoryNames) {
                $query->where('public', true)
                    ->orWhereHas('shares', function ($query) {
                        $query->where('user_id', Auth::user()->id);
                    });
            })
                ->whereHas('categories', function ($query) use ($categoryNames) {
                    $query->whereIn('name', $categoryNames);
                })
                ->where('id', '!=', $post->id)
                ->get();

            $rankedPosts = $similarPosts->map(function ($similarPost) use ($categoryNames) {
                $similarPostCategories = $similarPost->categories->pluck('name');
                $commonCategories = $categoryNames->intersect($similarPostCategories);
                $count = $commonCategories->count();

                return [
                    'post' => new PostsResource($similarPost),
                    'category_count' => $count,
                ];
            })->sortByDesc('category_count')->values();

            $data = [
                'post' => new PostsResource($post),
                'similar_posts' => $rankedPosts,
            ];

            return $data;
        }

        return null;
    }
}
