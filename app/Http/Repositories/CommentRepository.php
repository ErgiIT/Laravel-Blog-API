<?php

namespace App\Http\Repositories;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class CommentRepository
{
    public function index($postId)
    {
        $post = Post::find($postId);

        if (!$post) {
            throw new \Exception('Comments not found', 404);
        }

        if ($post->public === 0 && $post->user_id !== Auth::user()->id) {
            throw new \Exception('You do not have access to this post', 404);
        }

        $comments = $post->comments;

        return $comments;
    }

    public function show($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            throw new \Exception('Comment not found', 404);
        }

        return $comment;
    }

    public function store($postId, array $data)
    {
        $post = Post::find($postId);
        $userId = auth()->id();

        if ($post->public === 0 && !$post->shares()->where('user_id', $userId)->exists() && $post->user_id !== $userId) {
            throw new \Exception('You are not authorized to make this request', 403);
        }

        $comment = Comment::create([
            'user_id' => $userId,
            'post_id' => $postId,
            'comment' => $data['comment'],
        ]);

        return $comment;
    }


    public function update($id, array $data)
    {
        $comment = Comment::find($id);

        if (!$comment || $comment->user_id !== auth()->id()) {
            throw new \Exception('Comment not found', 404);
        }

        $comment->comment = $data['comment'];
        $comment->save();

        return $comment;
    }

    public function destroy($id)
    {
        $comment = Comment::find($id);

        if (!$comment) {
            throw new \Exception('Comment not found', 404);
        }

        $comment->delete();
    }
}
