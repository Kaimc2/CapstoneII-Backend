<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $owner = User::find($this->owner_id);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'ownerId' => $this->owner_id,
            'ownerName' => $owner->name,
            'description' => $this->description,
            'tailorThumbnail' => $this->tailor_thumbnail ? env('BACKEND_URL') . Storage::url($this->tailor_thumbnail) : null,
            'address' => $this->address,
            'email' => $this->email,
            'phoneNumber' => $this->phone_number,
            'materials' => $this->materials,
            'colors' => $this->colors,
            'sizes' => $this->sizes,
            'updatedAt' => Carbon::parse($this->updated_at)->diffForHumans(),
        ];
    }
}
