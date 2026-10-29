<?php

namespace Modules\Countries\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    protected $fillable = ['code', 'name', 'iso3', 'numeric_code', 'phonecode', 'capital', 'currency', 'region', 'subregion', 'latitude', 'longitude', 'flag', 'wikiDataId'];

    public function countryStates()
    {
        return $this->hasMany(CountryState::class, 'country_id');
    }

    public function cities()
    {
        return $this->hasMany(City::class, 'country_id');
    }

    public $timestamps = false;
}