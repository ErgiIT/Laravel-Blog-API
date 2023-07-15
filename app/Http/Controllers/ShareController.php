<?php

namespace App\Http\Controllers;

use App\Http\Repositories\ShareRepository;
use App\Http\Requests\StoreShareRequest;
use App\Http\Requests\UpdateShareRequest;
use App\Traits\HttpResponses;

class ShareController extends Controller
{
    use HttpResponses;

    protected $shareRepository;

    public function __construct(ShareRepository $shareRepository)
    {
        $this->shareRepository = $shareRepository;
    }


    public function store(StoreShareRequest $request, $postId)
    {
        try {
            $validatedData = $request->validated();

            $share = $this->shareRepository->store($postId, $validatedData);

            return $this->success($share, 'Share created successfully', 201);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }

    // public function update(UpdateShareRequest $request, $id)
    // {
    //     try {
    //         $validatedData = $request->validated();

    //         $share = $this->shareRepository->update($id, $validatedData);

    //         return $this->success($share, 'Share updated successfully');
    //     } catch (\Exception $e) {
    //         return $this->error(null, $e->getMessage(), $e->getCode());
    //     }
    // }

    public function destroy($id)
    {
        try {
            $this->shareRepository->destroy($id);

            return $this->success(null, 'Share deleted successfully');
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), $e->getCode());
        }
    }
}
