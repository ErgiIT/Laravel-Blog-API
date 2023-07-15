<?php

namespace App\Http\Repositories;

use App\Mail\PostShared;
use App\Models\Post;
use App\Models\Share;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ShareRepository
{
    public function store($postId, array $data)
    {
        $user = Auth::user();
        $post = Post::findOrFail($postId);

        // Check if the authenticated user is the owner of the post
        if ($user->id !== $post->user_id) {
            throw new \Exception('You are not allowed to share this post', 403);
        }

        // Retrieve the user(s) to share the post with
        $shareWithUserIds = $data['user_ids'];

        foreach ($shareWithUserIds as $shareWithUserId) {
            // Check if the user with the given ID exists
            $userExists = User::where('id', $shareWithUserId)->exists();

            if (!$userExists) {
                throw new \Exception('User with ID ' . $shareWithUserId . ' does not exist.', 404);
            }

            if ($shareWithUserId === $user->id) {
                throw new \Exception('You cannot share the post with yourself.', 404);
            }

            $existingShare = Share::where('user_id', $shareWithUserId)
                ->where('post_id', $post->id)
                ->exists();

            if ($existingShare) {
                throw new \Exception("This post has already been shared with user: " . $shareWithUserId, 404);
            }
        }

        $successfulShares = [];

        // Create shares and send emails (if needed) outside the loop
        foreach ($shareWithUserIds as $shareWithUserId) {
            $share = Share::create([
                'user_id' => $shareWithUserId,
                'post_id' => $post->id,
            ]);

            // Get the user's email address
            $userEmail = User::where('id', $shareWithUserId)->value('email');

            // Send the email
            Mail::to($userEmail)->send(new PostShared($share));

            $successfulShares[] = $shareWithUserId;
        }

        // Convert the array of user IDs into a string
        $successfulSharesString = implode(', ', $successfulShares);

        // Return the success message with the user IDs
        return "Post shared successfully with users: " . $successfulSharesString;
    }



    // public function update($id, array $data)
    // {
    //     $user = Auth::user();
    //     $share = Share::find($id);

    //     if (!$share) {
    //         throw new \Exception('Share not found', 404);
    //     }

    //     $post = $share->post;

    //     // Check if the authenticated user is the owner of the post
    //     if ($user->id !== $post->user_id) {
    //         throw new \Exception('You are not allowed to update this share', 403);
    //     }

    //     // Retrieve the user ID to update the share with
    //     $shareWithUserId = $data['user_id'];

    //     // Check if the user with the given ID exists
    //     $userExists = User::where('id', $shareWithUserId)->exists();

    //     if (!$userExists) {
    //         throw new \Exception('User with ' . $shareWithUserId . "does not exist", 403);
    //     }

    //     if ($shareWithUserId === $user->id) {
    //         throw new \Exception('You cannot share this post with yourself', 400);
    //     }


    //     // Check if the share record already exists for the given user and post combination
    //     $existingShare = Share::where('user_id', $shareWithUserId)
    //         ->where('post_id', $post->id)
    //         ->exists();

    //     if ($existingShare) {
    //         throw new \Exception('This post has already been shared with user ID: ' . $shareWithUserId, 400);
    //     }

    //     // Update the share record
    //     $share->update(['user_id' => $shareWithUserId]);

    //     return $share;
    // }

    public function destroy($shareId)
    {
        $user = Auth::user();
        $share = Share::find($shareId);

        if (!$share) {
            throw new \Exception('Share not found', 404);
        }

        $post = $share->post;

        // Check if the authenticated user is the owner of the post
        if ($user->id !== $post->user_id) {
            throw new \Exception('You are not allowed to delete this share', 403);
        }

        // Delete the share record
        $share->delete();
    }
}
