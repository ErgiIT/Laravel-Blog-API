<?php

namespace App\Http\Controllers;

use App\Http\Repositories\PostRepository;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Traits\HttpResponses;


class PostController extends Controller
{
    use HttpResponses;

    protected $postRepository;

    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }

    public function index($own = null)
    {
        try {
            $posts = $this->postRepository->index($own);

            return $this->success($posts);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function show($id)
    {
        try {
            $post = $this->postRepository->show($id);

            return $this->success($post);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function store(StorePostRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $post = $this->postRepository->store($validatedData);

            return $this->success($post, 'Post created successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function update($id, UpdatePostRequest $request)
    {
        try {
            $validatedData = $request->validated();

            $post = $this->postRepository->update($id, $validatedData);

            return $this->success($post, 'Post updated successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {
            $this->postRepository->destroy($id);

            return $this->success(null, 'Post deleted successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }
}
