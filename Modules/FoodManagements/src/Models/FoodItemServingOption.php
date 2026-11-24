<?php

namespace Modules\FoodManagements\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodItemServingOption extends Model
{
    use HasFactory;

    public const SERVE_TYPES = [
        'tray' => 'Tray',
        'plate' => 'Plate',
        'bowl' => 'Bowl',
        'box' => 'Box / container',
        'portion' => 'Portion',
        'piece' => 'Piece / item',
        'by_weight' => 'By weight',
        'by_volume' => 'By volume',
        'per_person' => 'Per person',
        'package' => 'Package / combo',
    ];

    public const FIXED_SIZE_TYPES = ['tray', 'plate', 'bowl', 'box', 'portion', 'piece', 'by_weight', 'by_volume'];
    public const WEIGHT_UNITS = ['Kg', 'Grams', 'Oz', 'Lb'];
    public const VOLUME_UNITS = ['Litres', 'Ml', 'Fl oz', 'Cups'];
    public const UNITS_BY_SERVE_TYPE = [
        'tray' => ['Trays', 'Pieces'],
        'plate' => ['Plates'],
        'bowl' => ['Bowls'],
        'box' => ['Boxes', 'Pieces'],
        'portion' => ['Portions', 'Pieces'],
        'piece' => ['Pieces'],
        'by_weight' => self::WEIGHT_UNITS,
        'by_volume' => self::VOLUME_UNITS,
        'per_person' => [],
        'package' => [],
    ];

    protected $fillable = [
        'food_item_id',
        'serving_name',
        'serve_type',
        'min_guests',
        'max_guests',
        'quantity',
        'unit',
        'serves',
        'price',
        'discount_price',
        'is_available',
        'sort_order',
    ];

    protected $casts = [
        'min_guests' => 'integer',
        'max_guests' => 'integer',
        'quantity' => 'decimal:2',
        'serves' => 'integer',
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'is_available' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function foodItem(): BelongsTo
    {
        return $this->belongsTo(FoodItem::class);
    }
}
