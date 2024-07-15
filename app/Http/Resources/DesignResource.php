<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DesignResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'userId' => $this->user_id,
            'frontContent' => $this->front_content,
            'backContent' => $this->back_content,
            'status' => $this->status,
            'updatedAt' => Carbon::parse($this->updated_at)->diffForHumans(),
        ];
    }
}
