<?php

namespace Modules\MobileConfiguration\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\Category;

class MobileBanner extends Model
{
    protected $table = 'mobile_banners';

    protected $fillable = [
        'name',
        'type',
        'type_value',
        'status',
        'position',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    // Relations
    public function product()
    {
        return $this->belongsTo(Product::class, 'type_value');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'type_value');
    }

    // Accessors
    public function getLinkDisplayAttribute()
    {
        switch ($this->type) {
            case 'product':
                return $this->product ? $this->product->name : 'Product not found';
            case 'category':
                return $this->category ? $this->category->name : 'Category not found';
            case 'external_link':
                return $this->type_value;
            default:
                return 'No Link';
        }
    }
}