<?php

namespace Modules\MobileConfiguration\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FeatureResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'used_for' => $this->used_for,
            'type' => $this->type,
            'selection_type' => $this->selection_type,
            'custom_items' => $this->custom_items,
            'items_count' => $this->items_count,
            'position' => $this->position,
            'status' => $this->status,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}