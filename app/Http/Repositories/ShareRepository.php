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

        $alreadySharedWithUsers = []; // Array to store user IDs with existing shares
        $successfulShares = []; // Array to store user IDs for successful shares
        $errorMessages = []; // Array to store error messages

        foreach ($shareWithUserIds as $shareWithUserId) {
            // Check if the user with the given ID exists
            $userExists = User::where('id', $shareWithUserId)->exists();

            if (!$userExists) {
                $errorMessages[] = 'User with ID ' . $shareWithUserId . ' does not exist.';
                continue; // Skip to the next iteration of the loop
            }

            if ($shareWithUserId === $user->id) {
                $errorMessages[] = 'You cannot share the post with yourself.';
                continue; // Skip to the next iteration of the loop
            }

            // Check if the share record already exists for the given user and post combination
            $existingShare = Share::where('user_id', $shareWithUserId)
                ->where('post_id', $post->id)
                ->exists();

            if ($existingShare) {
                $alreadySharedWithUsers[] = $shareWithUserId;
                continue; // Skip to the next iteration of the loop
            }

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

        if (!empty($errorMessages) || !empty($alreadySharedWithUsers) || !empty($successfulShares)) {
            $message = '';

            // Combine the error messages into a single string
            if (!empty($errorMessages)) {
                $errorMessage = implode(' ', $errorMessages);
                $message .= 'Errors occurred while sharing the post: ' . $errorMessage . ' ';
            }

            // If alreadySharedWithUsers array is not empty, display the already shared message
            if (!empty($alreadySharedWithUsers)) {
                $message .= 'This post has already been shared with user ID(s): ' . implode(', ', $alreadySharedWithUsers) . '. ';
            }

            // If successfulShares array is not empty, display the success message
            if (!empty($successfulShares)) {
                $successMessage = 'Post shared successfully with user ID(s): ' . implode(', ', $successfulShares) . '. ';
                $message .= $successMessage;
            }

            if (!empty($errorMessages) || !empty($alreadySharedWithUsers) && !empty($successfulShares)) {
                throw new \Exception($message, 404);
            } else {
                return  $message;
            }
        } else {
            throw new \Exception('No shares were made', 409);
        }
    }

    public function update($id, array $data)
    {
        $user = Auth::user();
        $share = Share::find($id);

        if (!$share) {
            throw new \Exception('Share not found', 404);
        }

        $post = $share->post;

        // Check if the authenticated user is the owner of the post
        if ($user->id !== $post->user_id) {
            throw new \Exception('You are not allowed to update this share', 403);
        }

        // Retrieve the user ID to update the share with
        $shareWithUserId = $data['user_id'];

        // Check if the user with the given ID exists
        $userExists = User::where('id', $shareWithUserId)->exists();

        if (!$userExists) {
            throw new \Exception('User with ' . $shareWithUserId . "does not exist", 403);
        }

        if ($shareWithUserId === $user->id) {
            throw new \Exception('You cannot share this post with yourself', 400);
        }


        // Check if the share record already exists for the given user and post combination
        $existingShare = Share::where('user_id', $shareWithUserId)
            ->where('post_id', $post->id)
            ->exists();

        if ($existingShare) {
            throw new \Exception('This post has already been shared with user ID: ' . $shareWithUserId, 400);
        }

        // Update the share record
        $share->update(['user_id' => $shareWithUserId]);

        return $share;
    }

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
