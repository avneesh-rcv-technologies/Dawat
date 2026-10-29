<?php

namespace Modules\Currencies\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrencySeeder extends Seeder
{
    public function run()
    {
        $currency = DB::table('currencies')->where('code', 'USD')->first();

        $data = [
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'decimal' => 2,
            'rate' => 1.000000000000,
            'group_separator' => ',',
            'decimal_separator' => '.',
            'currency_position' => 'left',
            'default_currency' => true,
            'updated_at' => now(),
        ];

        if ($currency) {
            DB::table('currencies')
                ->where('id', $currency->id)
                ->update($data);
        } else {
            $data['created_at'] = now();
            DB::table('currencies')->insert($data);
        }
    }
}