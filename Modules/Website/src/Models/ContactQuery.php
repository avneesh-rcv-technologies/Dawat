<?php

namespace Modules\Website\Models;

use Illuminate\Database\Eloquent\Model;

class ContactQuery extends Model
{
    protected $fillable = ['first_name', 'last_name', 'email', 'message'];
}
