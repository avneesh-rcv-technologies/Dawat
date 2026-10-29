<?php

namespace Modules\FoodManagements\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FoodImage extends Model
{
    use HasFactory;

    protected $table = 'food_images';

    protected $fillable = [
        'food_item_id',
        'image',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function foodItem()
    {
        return $this->belongsTo(FoodItem::class);
    }
}
