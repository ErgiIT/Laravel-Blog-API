<?php

namespace App\Http\Repositories;

use App\Http\Resources\PostsResource;
use App\Models\Post;
use App\Models\Share;
use Illuminate\Support\Facades\Auth;

class PostRepository
{

    public function index($userId)
    {
        if ($userId) {
            return PostsResource::collection(
                Post::where("user_id", $userId)->get()
            );
        }

        $userId = Auth::user() ? Auth::user()->id : null;

        return PostsResource::collection(
            Post::where(function ($query) use ($userId) {
                $query->where("public", 1)
                    ->orWhere("user_id", $userId);
            })->orWhereHas('shares', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->get()
        );
    }

    public function show($id)
    {
        $loggedInUserId = Auth::user()->id;

        $post = Post::where('id', $id)
            ->where(function ($query) use ($loggedInUserId) {
                $query->where('user_id', $loggedInUserId)
                    ->orWhereHas('shares', function ($query) use ($loggedInUserId) {
                        $query->where('user_id', $loggedInUserId);
                    });
            })
            ->first();

        if ($post) {
            $categoryNames = $post->categories->pluck('name');

            $similarPosts = Post::where(function ($query) use ($categoryNames) {
                $query->where('public', true) // Public posts
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

        throw new \Exception('Post not found', 404);
    }

    public function store(array $data)
    {
        $post = Post::create([
            "user_id" => Auth::user()->id,
            "title" => $data['title'],
            "desc" => $data['desc'],
            "public" => $data['public']
        ]);

        $categories = $data['categories'];
        $post->categories()->attach($categories);
        $post->last_edited_by = Auth::user()->id;
        $post->save();

        Share::create([
            'user_id' => Auth::user()->id,
            'post_id' => $post->id,
        ]);

        return new PostsResource($post);
    }


    public function update($id, array $data)
    {
        $loggedInUserId = Auth::user()->id;
        $post = Post::find($id);

        if (!$post) {
            throw new \Exception('Post not found', 404);
        }

        if (Auth::user()->id == $post->user_id) {
            $categories = $data['categories'];
            $post->categories()->sync($categories);

            $post->last_edited_by = $loggedInUserId;

            $post->update($data);
            return new PostsResource($post);
        }

        if (!$post->shares()->where('user_id', $loggedInUserId)->exists() && $post->user_id !== $loggedInUserId) {
            throw new \Exception('You are not authorized to see this request', 404);
        }

        if ($this->isNotAuthorized($post) && $post->public === 0) {
            throw new \Exception('You cannot update a private post', 404);
        }

        $validatedData = ["title" => $data["title"], "desc" => $data["desc"]];

        $post->fill($validatedData);
        $post->last_edited_by = $loggedInUserId;
        $post->save();

        $post->refresh();

        return new PostsResource($post);
    }

    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            throw new \Exception('Post not found', 404);
        }

        if ($this->isNotAuthorized($post)) {
            throw new \Exception('You are not authorized to make this request', 403);
        }

        $post->delete();
    }

    private function isNotAuthorized($post)
    {
        return Auth::user()->id !== $post->user_id;
    }
}
