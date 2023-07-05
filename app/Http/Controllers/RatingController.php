<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    use HttpResponses;
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $rating = Rating::find($id);

        if ($rating) {
            return $this->success($rating);
        } else {
            return $this->error(null, 'Rating not found', 404);
        }
    }

    public function upsert($post_id, Request $request)
    {
        $request->validate([
            'rating' => ['required', 'numeric', 'min:1', 'max:5']
        ]);

        $user_id = Auth::user()->id;

        // Check if the user has already rated the post
        $existingRating = Rating::where('user_id', $user_id)
            ->where('post_id', $post_id)
            ->first();

        if ($existingRating) {
            // Update the existing rating
            $existingRating->rating = $request->input('rating');
            $existingRating->save();

            return $this->success($existingRating, 'Rating updated successfully');
        }

        // Create a new rating
        $rating = Rating::create([
            'user_id' => $user_id,
            'post_id' => $post_id,
            'rating' => $request->input('rating'),
        ]);

        return $this->success($rating, 'Rating created successfully', 201);
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $rating = Rating::find($id);

        if ($rating) {
            $rating->delete();

            return $this->success(null, 'Rating deleted successfully');
        } else {
            return $this->error(null, 'Rating not found', 404);
        }
    }
}
