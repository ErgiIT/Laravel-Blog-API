<?php

namespace App\Http\Controllers;

use App\Http\Repositories\RatingRepository;
use App\Http\Requests\UpsertRatingRequest;
use App\Models\Rating;
use App\Traits\HttpResponses;


class RatingController extends Controller
{
    use HttpResponses;
    protected $ratingRepository;

    public function __construct(RatingRepository $ratingRepository)
    {
        $this->ratingRepository = $ratingRepository;
    }

    public function index($postId)
    {
        try {
            $ratings = $this->ratingRepository->index($postId);

            return $this->success($ratings);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function show($id)
    {
        try {
            $rating = $this->ratingRepository->show($id);

            return $this->success($rating);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function upsert($postId, UpsertRatingRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $rating = $this->ratingRepository->upsert($postId, $validatedData);

            return $this->success($rating, 'Rating upserted successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

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
