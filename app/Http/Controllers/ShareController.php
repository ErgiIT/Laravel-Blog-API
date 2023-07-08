<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Post;
use App\Models\User;
use App\Models\Share;
use App\Traits\HttpResponses; // Import the HttpResponses trait

class ShareController extends Controller
{
    use HttpResponses; // Use the HttpResponses trait

    public function store(Request $request, $postId)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'numeric',
        ]);

        $user = Auth::user();
        $post = Post::findOrFail($postId);

        // Check if the authenticated user is the owner of the post
        if ($user->id !== $post->user_id) {
            return $this->error(null, 'You are not allowed to share this post.', 403);
        }

        // Retrieve the user(s) to share the post with
        $shareWithUserIds = $request->input('user_ids', []);

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

            // Check if the post is public
            if (!$post->public) {
                $errorMessages[] = 'This post cannot be shared as it is not public.';
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

            Share::create([
                'user_id' => $shareWithUserId,
                'post_id' => $post->id,
            ]);

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

            if (!empty($errorMessages)) {
                return $this->error(null, $message, 400);
            } else {
                return $this->success(null, $message);
            }
        } else {
            return $this->error(null, 'No shares were made.', 400);
        }
    }
}
