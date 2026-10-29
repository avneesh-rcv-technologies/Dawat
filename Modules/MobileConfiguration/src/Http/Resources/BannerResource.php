<?php

namespace Modules\MobileConfiguration\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'type_value' => $this->type_value,
            'link_display' => $this->link_display,
            'status' => $this->status,
            'position' => $this->position,
            'image' => $this->getImageUrl(),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function getImageUrl()
    {
        // Get image using Filemanager helper
        $image = fn_get_image('mobile_banner', $this->id);
        
        if ($image) {
            return $image['url'];
        }

        return null;
    }
}