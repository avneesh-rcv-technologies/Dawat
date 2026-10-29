<?php

namespace Modules\MobileConfiguration\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Cms\Models\CmsPage;

class MobileCms extends Model
{
    protected $table = 'mobile_cms';

    protected $fillable = [
        'name',
        'cms_page_id',
        'status',
        'position',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Relations
    public function cmsPage()
    {
        return $this->belongsTo(CmsPage::class, 'cms_page_id');
    }

    // Get image using Filemanager helper
    public function getImageAttribute()
    {
        return fn_get_image('mobile_cms', $this->id);
    }

    // Get image URL
    public function getImageUrlAttribute()
    {
        $image = $this->image;
        if ($image) {
            return asset('storage/' . $image->path . '/' . $image->file_name);
        }
        return null;
    }

    // Get page title from related CMS page
    public function getPageTitleAttribute()
    {
        return $this->cmsPage ? decode_html_entities($this->cmsPage->page_title) : 'Page not found';
    }

    // Get page slug from related CMS page
    public function getPageSlugAttribute()
    {
        return $this->cmsPage ? $this->cmsPage->slug : null;
    }
}