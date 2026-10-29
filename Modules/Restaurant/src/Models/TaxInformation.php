<?php

namespace Modules\Restaurant\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Countries\Models\City;
use Modules\Countries\Models\Country;
use Modules\Countries\Models\CountryState;

class TaxInformation extends Model
{
    protected $table = 'tax_information';

    protected $fillable = [
        'tax_type', 'full_name', 'ssn', 'company_legal_name', 'doing_business_as', 'ein',
        'address', 'city_id', 'state_id', 'zip_code', 'country_id',
        'same_mailing_address', 'mailing_address', 'mailing_city', 'mailing_state', 'mailing_zip_code',
        'receive_1099_electronically', 'backup_tax_withholding', 'tax_certification', 'tax_certified_at',
    ];

    protected $hidden = ['ssn', 'ein'];

    protected $casts = [
        'city_id' => 'integer',
        'state_id' => 'integer',
        'country_id' => 'integer',
        'ssn' => 'encrypted',
        'ein' => 'encrypted',
        'same_mailing_address' => 'boolean',
        'receive_1099_electronically' => 'boolean',
        'backup_tax_withholding' => 'boolean',
        'tax_certification' => 'boolean',
        'tax_certified_at' => 'datetime',
    ];

    public function taxable(): MorphTo
    {
        return $this->morphTo();
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(CountryState::class, 'state_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
}
