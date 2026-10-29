<?php

namespace Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Mockery;
use Modules\Restaurant\Enums\OnboardingStep;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\OnboardingStepReviewService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OnboardingStartReviewTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Facade::setFacadeApplication(new Container());
        DB::swap(Mockery::mock(\Illuminate\Database\DatabaseManager::class));
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());
        Validator::swap(new Factory(new Translator(new ArrayLoader(), 'en')));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        parent::tearDown();
    }

    public static function startableSteps(): array
    {
        return [
            'registration submitted' => [OnboardingStep::REGISTRATION, 'submitted'],
            'registration pending' => [OnboardingStep::REGISTRATION, 'pending'],
            'banking submitted' => [OnboardingStep::BANKING, 'submitted'],
        ];
    }

    #[DataProvider('startableSteps')]
    public function test_start_review_updates_only_the_step(OnboardingStep $key, string $status): void
    {
        [$service, $restaurant, $step] = $this->reviewService($key, $status);
        $step->shouldReceive('update')->once()->with([
            'status' => 'under_review', 'reviewed_by' => 8,
            'reviewed_at' => null, 'rejection_reason' => null,
        ]);
        $restaurant->shouldNotReceive('update');
        $this->assertSame($restaurant, $service->startReview(16, 8, $key));
    }

    public static function unstartableStatuses(): array
    {
        return [['draft'], ['approved'], ['rejected'], ['under_review']];
    }

    #[DataProvider('unstartableStatuses')]
    public function test_start_review_rejects_invalid_statuses(string $status): void
    {
        [$service, $restaurant, $step] = $this->reviewService(OnboardingStep::REGISTRATION, $status);
        $step->shouldNotReceive('update');
        $restaurant->shouldNotReceive('update');
        $this->expectException(ValidationException::class);
        $service->startReview(16, 8, OnboardingStep::REGISTRATION);
    }

    public static function prematureDecisions(): array
    {
        return [
            [OnboardingStep::REGISTRATION, true], [OnboardingStep::REGISTRATION, false],
            [OnboardingStep::BANKING, true], [OnboardingStep::BANKING, false],
        ];
    }

    #[DataProvider('prematureDecisions')]
    public function test_approve_and_reject_require_start_review(OnboardingStep $key, bool $approved): void
    {
        [$service, $restaurant, $step] = $this->reviewService($key, 'submitted');
        $step->shouldNotReceive('update');
        $restaurant->shouldNotReceive('update');
        $this->expectException(ValidationException::class);
        if ($key === OnboardingStep::REGISTRATION) {
            $service->reviewRegistration(16, 8, $approved, 'Please correct these details.');
        } else {
            $service->reviewBanking(16, 8, $approved, 'Please correct these details.');
        }
    }

    #[DataProvider('prematureDecisions')]
    public function test_started_review_can_be_approved_or_rejected(OnboardingStep $key, bool $approved): void
    {
        [$service, $restaurant, $step] = $this->reviewService($key, 'under_review');
        $step->shouldReceive('update')->once()->with(Mockery::on(function ($values) use ($approved) {
            $this->assertSame($approved ? 'approved' : 'rejected', $values['status']);
            $this->assertSame(8, $values['reviewed_by']);
            $this->assertNotNull($values['reviewed_at']);
            $this->assertSame($approved ? null : 'Please correct these details.', $values['rejection_reason']);
            return true;
        }));
        if ($key === OnboardingStep::REGISTRATION) {
            $restaurant->shouldReceive('update')->once()->with(Mockery::on(function ($values) use ($approved) {
                $this->assertSame($approved ? 'approved' : 'rejected', $values['approval_status']);
                return true;
            }));
            $result = $service->reviewRegistration(16, 8, $approved, 'Please correct these details.');
        } else {
            $restaurant->shouldReceive('getAttribute')->with('admin')->andReturnNull();
            DB::shouldReceive('afterCommit')->once();
            $result = $service->reviewBanking(16, 8, $approved, 'Please correct these details.');
        }
        $this->assertSame($restaurant, $result);
    }

    private function reviewService(OnboardingStep $key, string $status): array
    {
        $restaurants = Mockery::mock(Restaurant::class);
        $query = Mockery::mock();
        $restaurants->shouldReceive('newQuery')->once()->andReturn($query);
        $query->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $restaurant = Mockery::mock(Restaurant::class);
        $query->shouldReceive('findOrFail')->once()->with(16)->andReturn($restaurant);
        $relation = Mockery::mock(\Illuminate\Database\Eloquent\Relations\HasMany::class);
        $restaurant->shouldReceive('onboardingSteps')->once()->andReturn($relation);
        $relation->shouldReceive('where')->with('step_key', $key->value)->andReturnSelf();
        $relation->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $step = Mockery::mock();
        $step->status = $status;
        $relation->shouldReceive('first')->once()->andReturn($step);
        if ($key === OnboardingStep::BANKING) {
            $banking = Mockery::mock(\Illuminate\Database\Eloquent\Relations\HasOne::class);
            $restaurant->shouldReceive('bankingDetail')->andReturn($banking);
            $banking->shouldReceive('exists')->andReturnTrue();
        }
        return [new OnboardingStepReviewService($restaurants), $restaurant, $step];
    }
}
