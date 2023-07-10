<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    use HttpResponses;

    /**
     * Store a newly created resource in storage.
     *
     * @param  int  $post_id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($post_id, Request $request)
    {
        $request->validate([
            'comment' => ['required', 'max:255']
        ]);

        $comment = Comment::create([
            'user_id' => Auth::user()->id,
            'post_id' => $post_id,
            'comment' => $request->comment,
        ]);

        return $this->success($comment, 'Comment created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $comment = Comment::find($id);

        if ($comment) {
            return $this->success($comment);
        } else {
            return $this->error(null, 'Comment not found', 404);
        }
    }

    public function index($id)
    {
        $post = Post::find($id);

        if ($post && $post->public === 1) {
            $comments = $post->comments;
            return $this->success($comments);
        } else {
            return $this->error(null, 'Comment not found', 404);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
        $request->validate([
            'comment' => ['required', 'max:255']
        ]);

        $comment = Comment::find($id);

        if ($comment && $comment->user_id === Auth::user()->id) {
            $comment->comment = $request->input('comment');
            $comment->save();

            return $this->success($comment, 'Comment updated successfully');
        } else {
            return $this->error(null, 'Comment not found', 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $comment = Comment::find($id);

        if ($comment) {
            $comment->delete();

            return $this->success(null, 'Comment deleted successfully');
        } else {
            return $this->error(null, 'Comment not found', 404);
        }
    }
}
