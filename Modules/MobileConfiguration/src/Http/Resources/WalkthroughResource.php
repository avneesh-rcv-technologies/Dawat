<?php

namespace Modules\MobileConfiguration\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WalkthroughResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
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
        $image = fn_get_image('mobile_walkthrough', $this->id);

        if ($image) {
            return $image['url'];
        }

        return null;
    }
}