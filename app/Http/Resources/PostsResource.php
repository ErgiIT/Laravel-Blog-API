<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => (string)$this->id,
            "attributes" => [
                "title" => $this->title,
                "desc" => $this->desc,
                "public" => $this->public,
                "created_at" => $this->created_at,
                "updated_at" => $this->updated_at
            ],
            "relationships" => [
                "id" => (string)$this->user->id,
                "user name" => $this->user->name,
                "user email" => $this->user->email,
                "post comments" => $this->comments
            ]
        ];
    }
}
