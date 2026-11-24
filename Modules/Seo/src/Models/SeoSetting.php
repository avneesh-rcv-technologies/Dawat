<?php

namespace Modules\Seo\Models;

use Illuminate\Database\Eloquent\Model;

class SeoSetting extends Model
{
    protected $table = 'seo_settings';

    protected $fillable = [
        'key',
        'value',
    ];
}
