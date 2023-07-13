<?php

namespace App\Http\Controllers;

use App\Http\Repositories\CommentRepository;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Traits\HttpResponses;

class CommentController extends Controller
{
    use HttpResponses;

    protected $commentRepository;

    public function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }


    public function index($postId)
    {
        try {
            $comments = $this->commentRepository->index($postId);

            return $this->success($comments);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function show($id)
    {
        try {
            $comment = $this->commentRepository->show($id);

            return $this->success($comment);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function store($postId, StoreCommentRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $comment = $this->commentRepository->store($postId, $validatedData);

            return $this->success($comment, 'Comment created successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function update($id, UpdateCommentRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $comment = $this->commentRepository->update($id, $validatedData);

            return $this->success($comment, 'Comment updated successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {
            $this->commentRepository->destroy($id);

            return $this->success(null, 'Comment deleted successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }
}
