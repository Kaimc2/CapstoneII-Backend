<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
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
            'profilePicture' => $this->profile_picture ? env('BACKEND_URL') . Storage::url($this->profile_picture) : null,
            'phoneNumber' => $this->phone_number,
            'email' => $this->email,
            'accessToken' => $this->accessToken,
            'isVerified' => $this->email_verified_at ? true : false,
            'createdAt' => Carbon::parse($this->created_at)->diffForHumans(),
            'updatedAt' => Carbon::parse($this->updated_at)->diffForHumans(),
        ];
    }
}
