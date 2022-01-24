<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            "id"            => $this->id,
            'name'          => $this->name,
            "email"         => $this->email,
            "phone"         => $this->phone,
            "address"       => $this->address,
            "lat"           => $this->lat,
            "lng"           => $this->lng,
            "image"         => url('/') . '/' . $this->image,
            "created_at"    => $this->created_at->format('Y-m-d'),
            'type'          => $this->roles->pluck('name')->first(),
            'approved'      => $this->approved,
        ];
    }
}
