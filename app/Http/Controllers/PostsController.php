<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostsResource;
use App\Models\Post;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostsController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PostsResource::collection(
            Post::where("public", 1)->get()
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

        return $this->success(new PostsResource($post), 'Post created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        if ($this->isNotAuthorized($post)) {
            return $this->error(null, 'You are not authorized to make this request', 403);
        }

        return new PostsResource($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        if ($this->isNotAuthorized($post)) {
            return $this->error(null, 'You are not authorized to make this request', 403);
        }

        $post->update($request->all());

        return new PostsResource($post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
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
