<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        return parent::toArray($request);
        return [
            'id'=>$this->id,
            'title'=>$this->title,
            'description'=>$this->description,
            'created_at'=>$this->created_at,
            'due_date'=>$this->due_date,
//            'category'=> $this->whenLoaded('category')
            'category'=> new CategoryResource($this->whenLoaded('category')),
            'comments'=> CommentResource::collection($this->whenLoaded('comments')),
            'files' => FileResource::collection($this->whenLoaded('files'))

        ];
    }
}
