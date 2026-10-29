<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RestaurantHoliday extends Model
{
    use HasFactory;

    protected $table = 'restaurant_holidays';

    protected $fillable = [
        'restaurant_id',
        'date',
        'reason',
        'is_closed',
    ];

    protected $casts = [
        'date' => 'date',
        'is_closed' => 'boolean',
    ];

    /* ============================
     | Relationships
     |============================ */

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
