<?php

namespace Modules\FoodManagements\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FoodOption extends Model
{
    use HasFactory;

    protected $table = 'food_options';

    protected $fillable = [
        'food_option_group_id',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function optionGroup()
    {
        return $this->belongsTo(FoodOptionGroup::class);
    }

    public function foodItems()
    {
        return $this->belongsToMany(FoodItem::class, 'food_item_options')
                    ->withPivot('price')
                    ->withTimestamps();
    }

    public function getPriceForFoodItem($foodItemId)
    {
        $pivot = $this->foodItems()->where('food_item_id', $foodItemId)->first();
        return $pivot ? $pivot->pivot->price : 0;
    }

        protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => decode_html_entities($value)
        );
    }


}
