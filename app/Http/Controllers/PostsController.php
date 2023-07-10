<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostsResource;
use App\Models\Post;
use App\Models\Share;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostsController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index($id = null)
    {
        if ($id) {
            return PostsResource::collection(
                Post::where("user_id", $id)->get()
            );
        }

        $id = Auth::user() ? Auth::user()->id : null;

        return PostsResource::collection(
            Post::where(function ($query) use ($id) {
                $query->where("public", 1)
                    ->orWhere("user_id", $id);
            })->orWhereHas('shares', function ($query) use ($id) {
                $query->where('user_id', $id);
            })->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $validatedData = $request->validated();

        $post = Post::create([
            "user_id" => Auth::user()->id,
            "title" => $validatedData['title'],
            "desc" => $validatedData['desc'],
            "public" => $validatedData['public']
        ]);

        $categories = $validatedData['categories'];
        $post->categories()->attach($categories);
        $post->last_edited_by = Auth::user()->id;
        $post->save();

        Share::create([
            'user_id' => Auth::user()->id,
            'post_id' => $post->id,
        ]);

        return $this->success(new PostsResource($post), 'Post created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
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

            return $this->success($data, 'Post retrieved successfully');
        }

        return $this->error(null, 'You are not authorized to view this post', 403);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $loggedInUserId = Auth::user()->id;
        $post = Post::find($id);

        if (!$post) {
            return $this->error(null, 'Post not found', 404);
        }

        if (Auth::user()->id == $post->user_id) {
            $validatedData = $request->validate([
                "title" => ["sometimes", "max:255"],
                "desc" => ["sometimes", "max:255"],
                "public" => ["sometimes", "boolean"],
                'categories' => 'sometimes|array',
                'categories.*' => 'sometimes|numeric',
            ]);

            $categories = $validatedData['categories'];
            $post->categories()->sync($categories);

            $post->last_edited_by = $loggedInUserId;

            $post->update($validatedData);
            return $this->success(new PostsResource($post), 'Post updated successfully');
        }

        if (!$post->shares()->where('user_id', $loggedInUserId)->exists() && $post->user_id !== $loggedInUserId) {
            return $this->error(null, 'You are not authorized to make this request', 403);
        }

        if ($this->isNotAuthorized($post) && $post->public === 0) {
            return $this->error(null, 'You cannot update a private post', 403);
        }

        $validatedData = $request->validate([
            'title' => 'sometimes|max:255',
            'desc' => 'sometimes|max:255',
        ]);

        $post->update($validatedData);
        $post->last_edited_by = $loggedInUserId;
        $post->save();

        $post->refresh();

        return $this->success(new PostsResource($post), 'Post updated successfully');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return $this->error(null, 'Post not found', 404);
        }

        if ($this->isNotAuthorized($post)) {
            return $this->error(null, 'You are not authorized to make this request', 403);
        }

        $post->delete();

        return $this->success(null, 'Post deleted successfully');
    }


    private function isNotAuthorized($post)
    {
        return Auth::user()->id !== $post->user_id;
    }
}
