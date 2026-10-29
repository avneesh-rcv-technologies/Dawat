<?php

namespace Modules\GeneralSetting\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{

    protected $fillable = [
        'key',
        'value'
    ];

    protected $casts = [
        'value' => 'array',
    ];

   protected $table = 'settings';

   
}
