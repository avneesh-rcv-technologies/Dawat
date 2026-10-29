<?php

namespace Modules\MobileConfiguration\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MobileCmsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'cms_page_id' => $this->cms_page_id,
            'status' => $this->status,
            'position' => $this->position,
            'image' => $this->getImageUrl(),
            'page_title' => $this->page_title,
            'page_slug' => $this->page_slug,
            'cms_page' => $this->when($this->cmsPage, [
                'id' => $this->cmsPage?->id,
                'page_title' => $this->cmsPage?->page_title,
                'slug' => $this->cmsPage?->slug,
                'description' => $this->cmsPage?->description,
                'meta_title' => $this->cmsPage?->meta_title,
                'meta_description' => $this->cmsPage?->meta_description,
                'meta_keywords' => $this->cmsPage?->meta_keywords,
            ]),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function getImageUrl()
    {
        // Get image using Filemanager helper
        $image = fn_get_image('mobile_cms', $this->id);
        
       if ($image) {
            return $image['url'];
        }

        return null;
    }
}