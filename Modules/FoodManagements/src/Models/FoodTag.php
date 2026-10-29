<?php

namespace Modules\FoodManagements\Models;

use Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FoodTag extends Model
{
    use HasFactory;

    protected $table = 'food_tags';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function items()
    {
        return $this->belongsToMany(
            FoodItem::class,
            'food_item_tags'
        );
    }
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => decode_html_entities($value)
        );
    }


}
