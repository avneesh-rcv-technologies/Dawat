<?php

namespace Modules\Countries\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Countries\Models\CountryState as State;

class City extends Model
{
    protected $table = 'cities';

    protected $fillable = ['state_id', 'country_id', 'name', 'latitude', 'longitude', 'flag', 'wikiDataId'];

    public $timestamps = false;

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
}