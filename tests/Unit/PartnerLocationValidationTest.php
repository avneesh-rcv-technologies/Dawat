<?php

namespace Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\DatabasePresenceVerifierInterface;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Mockery;
use Modules\Restaurant\Http\Controllers\RestaurantOnboardingController;
use Modules\Restaurant\Services\RestaurantOnboardingOtpService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PartnerLocationValidationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        parent::tearDown();
    }

    public static function locations(): array
    {
        return [
            'valid hierarchy' => [['country_id' => 1, 'state_id' => 10, 'city_id' => 100], null],
            'wrong country' => [['country_id' => 2, 'state_id' => 10, 'city_id' => 100], 'state_id'],
            'wrong state' => [['country_id' => 1, 'state_id' => 20, 'city_id' => 100], 'city_id'],
            'missing city' => [['country_id' => 1, 'state_id' => 10], 'city_id'],
            'unknown city' => [['country_id' => 1, 'state_id' => 10, 'city_id' => 999], 'city_id'],
        ];
    }

    #[DataProvider('locations')]
    public function test_partner_form_validates_the_location_hierarchy(array $input, ?string $error): void
    {
        Facade::setFacadeApplication(new Container());
        $factory = new Factory(new Translator(new ArrayLoader(), 'en'));
        $presence = Mockery::mock(DatabasePresenceVerifierInterface::class);
        $presence->shouldReceive('setConnection');
        $presence->shouldReceive('getCount')->andReturnUsing(function ($table, $column, $id, $exclude, $idColumn, $extra) {
            $records = [
                'countries' => [1 => [], 2 => []],
                'country_states' => [10 => ['country_id' => 1], 20 => ['country_id' => 1]],
                'cities' => [100 => ['country_id' => 1, 'state_id' => 10]],
            ];
            $record = $records[$table][$id] ?? null;
            return $record !== null && !array_diff_assoc($extra, $record) ? 1 : 0;
        });
        $factory->setPresenceVerifier($presence);
        Validator::swap($factory);
        $request = Mockery::mock(Request::class);
        $request->shouldReceive('input')->with('email')->andReturnNull();
        foreach (['country_id', 'state_id'] as $key) {
            $request->shouldReceive('input')->with($key)->andReturn($input[$key] ?? null);
        }
        $request->shouldReceive('validate')->once()->andReturnUsing(function ($rules) use ($factory, $input, $error) {
            $validator = $factory->make($input, array_intersect_key($rules, array_flip(['country_id', 'state_id', 'city_id'])));
            if ($error) {
                $this->assertFalse($validator->passes());
                $this->assertTrue($validator->errors()->has($error));
            } else {
                $this->assertTrue($validator->passes());
            }
            // Stop before OTP/account creation; this test exercises the actual controller rules.
            throw new \RuntimeException('Location checked');
        });
        $otp = Mockery::mock(RestaurantOnboardingOtpService::class);
        $otp->shouldNotReceive('isVerified');
        $this->expectExceptionMessage('Location checked');
        (new RestaurantOnboardingController())->submit($request, $otp);
    }
}
