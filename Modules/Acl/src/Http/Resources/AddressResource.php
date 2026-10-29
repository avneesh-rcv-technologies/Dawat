<?php

namespace Modules\Acl\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'address_type' => $this->address_type,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'full_address' => $this->full_address,
            'city' => [
                'id' => $this->city?->id,
                'name' => $this->city?->name,
            ],
            'state' => [
                'id' => $this->state?->id,
                'name' => $this->state?->name,
            ],
            'country' => [
                'id' => $this->country?->id,
                'name' => $this->country?->name,
                'code' => $this->country?->code,
            ],
            'zip_code' => $this->zip_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'city_name' => $this->city_name, 
            'state_name' => $this->state_id, 
        ];
    }
}
