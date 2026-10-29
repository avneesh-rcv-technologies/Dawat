<?php

namespace Modules\Countries\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class CountryState extends Model
{
    use HasFactory;

    protected $fillable = ['country_id', 'country_code', 'code', 'name', 'latitude', 'longitude', 'flag', 'wikiDataId'];

    public $timestamps = false;

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function cities()
    {
        return $this->hasMany(City::class, 'state_id');
    }
}