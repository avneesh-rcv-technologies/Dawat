<?php

namespace Modules\FoodManagements\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FoodOptionGroup extends Model
{
    use HasFactory;

    protected $table = 'food_option_groups';

    protected $fillable = [
        'name',
        'is_required',
        'min',
        'max',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'min'         => 'integer',
        'max'         => 'integer',
    ];

    public function options()
    {
        return $this->hasMany(FoodOption::class);
    }

    public function foodItems()
    {
        return $this->belongsToMany(
            FoodItem::class,
            'food_item_option_groups'
        );
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value) => decode_html_entities($value)
        );
    }
}
