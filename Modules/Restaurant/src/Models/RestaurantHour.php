<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RestaurantHour extends Model
{
    use HasFactory;

    protected $table = 'restaurant_hours';

    protected $fillable = [
        'restaurant_id',
        'hours',
        'is_open_whole_day',
    ];

    protected $casts = [
        'hours' => 'array', // JSON → PHP array
        'is_open_whole_day' => 'boolean',
    ];

    /* ============================
     | Relationships
     |============================ */

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
