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
            Post::where("user_id", Auth::user()->id)->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $request->validated($request->all());

        $post = Post::create([
            "user_id" => Auth::user()->id,
            "title" => $request->title,
            "desc" => $request->desc,
            "public" => $request->public
        ]);

        return new PostsResource($post);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return $this->isNotAuthorized($post) ? $this->isNotAuthorized($post) : new PostsResource($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        if (Auth::user()->id !== $post->user_id) {
            return $this->error("", "You are not authorized to make this request", 403);
        }

        $post->update($request->all());

        return new PostsResource($post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        return $this->isNotAuthorized($post) ? $this->isNotAuthorized($post) : $post->delete();
    }

    private function isNotAuthorized($post)
    {
        if (Auth::user()->id !== $post->user_id) {
            return $this->error("", "You are not authorized to make this request", 403);
        }
    }
}
