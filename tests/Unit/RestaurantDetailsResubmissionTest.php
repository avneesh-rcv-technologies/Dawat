<?php

namespace Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\DatabasePresenceVerifierInterface;
use Illuminate\Validation\Factory;
use Mockery;
use Modules\Acl\Models\Admin;
use Modules\Notifications\Services\NotificationService;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Models\RestaurantBankingDetail;
use Modules\Restaurant\Models\TaxInformation;
use Modules\Restaurant\Services\RestaurantBankingService;
use Modules\Restaurant\Services\RestaurantTaxNotificationService;
use Modules\Restaurant\Services\RestaurantTaxService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class RestaurantDetailsResubmissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $container = new Container();
        Container::setInstance($container);
        Facade::setFacadeApplication($container);
        DB::swap(Mockery::mock(\Illuminate\Database\DatabaseManager::class));
        $validator = new Factory(new Translator(new ArrayLoader(), 'en'));
        $presence = Mockery::mock(DatabasePresenceVerifierInterface::class);
        $presence->shouldReceive('setConnection');
        $presence->shouldReceive('getCount')->andReturn(1);
        $validator->setPresenceVerifier($presence);
        Validator::swap($validator);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
        parent::tearDown();
    }

    public static function steps(): array
    {
        return [['banking'], ['tax']];
    }

    public static function mismatchedBankingLocations(): array
    {
        return [
            'country changed without matching state' => [['country_id' => 2], 'state_id'],
            'state changed without matching city' => [['state_id' => 2], 'city_id'],
            'city belongs to another country' => [['city_id' => 2], 'city_id'],
        ];
    }

    #[DataProvider('mismatchedBankingLocations')]
    public function test_partial_banking_updates_validate_saved_parent_and_child_ids(array $input, string $field): void
    {
        [$service, $restaurant, $record, $owner] = $this->fixture('banking');
        $presence = Mockery::mock(DatabasePresenceVerifierInterface::class);
        $presence->shouldReceive('setConnection');
        $presence->shouldReceive('getCount')->andReturnUsing(function ($table, $column, $id, $exclude, $idColumn, $extra) {
            $records = [
                'countries' => [1 => [], 2 => []],
                'country_states' => [1 => ['country_id' => 1], 2 => ['country_id' => 1]],
                'cities' => [1 => ['country_id' => 1, 'state_id' => 1], 2 => ['country_id' => 2, 'state_id' => 3]],
            ];
            $record = $records[$table][$id] ?? null;
            return $record !== null && !array_diff_assoc($extra, $record) ? 1 : 0;
        });
        Validator::getFacadeRoot()->setPresenceVerifier($presence);
        $record->shouldNotReceive('save');
        $restaurant->shouldNotReceive('update');
        DB::shouldNotReceive('afterCommit');
        try {
            (new \ReflectionMethod($service, 'persist'))->invokeArgs($service, [$restaurant, $owner, $input, false, true]);
            $this->fail('A mismatched banking location must be rejected.');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
        }
    }

    #[DataProvider('steps')]
    public function test_unchanged_owner_updates_reset_review_and_send_resubmitted_notification(string $kind): void
    {
        [$service, $restaurant, $record, $owner, $data, $notifications] = $this->fixture($kind);
        $restaurant->shouldReceive('update')->once()->with(['onboarding_status' => 'action_required'])->andReturnTrue();
        $steps = Mockery::mock(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        $restaurant->shouldReceive('onboardingSteps')->once()->andReturn($steps);
        $savedStep = Mockery::mock();
        $savedStep->wasRecentlyCreated = false;
        $savedStep->shouldReceive('wasChanged')->andReturnTrue();
        $steps->shouldReceive('updateOrCreate')->once()->with(['step_key' => $kind], Mockery::on(function ($values) {
            $this->assertSame('submitted', $values['status']);
            $this->assertNotNull($values['submitted_at']);
            foreach (['reviewed_at', 'reviewed_by', 'rejection_reason'] as $key) {
                $this->assertArrayHasKey($key, $values);
                $this->assertNull($values[$key]);
            }
            return true;
        }))->andReturn($savedStep);
        $record->shouldReceive('save')->once()->andReturnTrue();
        $event = $kind === 'banking' ? 'RestaurantBankingResubmitted' : 'RestaurantTaxResubmitted';
        if ($kind === 'banking') {
            $notifications->shouldReceive('trigger')->once()->with('Restaurant', $event, $owner, Mockery::type('array'))->andReturnTrue();
        } else {
            $notifications->shouldReceive('send')->once()->with($restaurant, $event, Mockery::type('array'));
        }
        DB::shouldReceive('afterCommit')->once()->andReturnUsing(fn ($callback) => $callback());
        $arguments = $kind === 'banking' ? [$restaurant, $owner, $data, false, true] : [$restaurant, $data, false];
        $this->assertSame($record, (new \ReflectionMethod($service, 'persist'))->invokeArgs($service, $arguments));
    }

    #[DataProvider('steps')]
    public function test_admin_edits_preserve_review_and_do_not_send_resubmissions(string $kind): void
    {
        [$service, $restaurant, $record, $owner, $data] = $this->fixture($kind);
        $restaurant->shouldNotReceive('onboardingSteps');
        DB::shouldNotReceive('afterCommit');
        $arguments = $kind === 'banking' ? [$restaurant, $owner, $data, false] : [$restaurant, $data, false, true];
        $this->assertSame($record, (new \ReflectionMethod($service, 'persist'))->invokeArgs($service, $arguments));
    }

    private function fixture(string $kind): array
    {
        $restaurant = Mockery::mock(Restaurant::class);
        $restaurant->shouldReceive('getAttribute')->with('tax_type')->andReturn('individual');
        $restaurant->shouldReceive('getAttribute')->with('name')->andReturn('Test Restaurant');
        $restaurant->shouldReceive('getAttribute')->with('id')->andReturn(12);
        $owner = new Admin();
        $record = Mockery::mock($kind === 'banking' ? RestaurantBankingDetail::class : TaxInformation::class);
        foreach (['country_id', 'state_id', 'city_id'] as $field) {
            $record->shouldReceive('getAttribute')->with($field)->andReturn(1);
        }
        $record->shouldReceive('fill')->andReturnSelf();
        $record->shouldReceive('isDirty')->andReturnFalse();
        $relation = Mockery::mock($kind === 'banking' ? \Illuminate\Database\Eloquent\Relations\HasOne::class : \Illuminate\Database\Eloquent\Relations\MorphMany::class);
        $restaurant->shouldReceive($kind === 'banking' ? 'bankingDetail' : 'taxInformation')->once()->andReturn($relation);
        $relation->shouldReceive('first')->once()->andReturn($record);
        $data = [];
        if ($kind === 'banking') {
            $notifications = Mockery::mock(NotificationService::class);
            $service = new RestaurantBankingService($notifications, $restaurant);
        } else {
            $data = ['tax_type' => 'individual', 'full_name' => 'Test Owner', 'ssn' => '123456789',
                'address' => 'Main Street', 'country_id' => 1, 'state_id' => 1, 'city_id' => 1,
                'zip_code' => '12345', 'same_mailing_address' => true,
                'receive_1099_electronically' => false, 'backup_tax_withholding' => false, 'tax_certification' => true];
            $record->shouldReceive('getFillable')->andReturn(array_keys($data));
            foreach ($data as $key => $value) {
                $record->shouldReceive('getAttribute')->with($key)->andReturn($value);
            }
            $record->shouldReceive('setAttribute')->with('tax_certified_at', Mockery::any())->andReturnSelf();
            $record->shouldReceive('fresh')->andReturnSelf();
            $relation->shouldReceive('where')->with('tax_type', 'individual')->once()->andReturnSelf();
            $notifications = Mockery::mock(RestaurantTaxNotificationService::class);
            app()->instance(RestaurantTaxNotificationService::class, $notifications);
            $service = new RestaurantTaxService($restaurant);
        }
        return [$service, $restaurant, $record, $owner, $data, $notifications];
    }
}
