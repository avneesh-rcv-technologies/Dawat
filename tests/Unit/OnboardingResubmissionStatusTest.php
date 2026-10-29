<?php

namespace Tests\Unit;

use Mockery;
use Modules\Restaurant\Enums\OnboardingStatus;
use Modules\Restaurant\Models\OnboardingStatusHistory;
use Modules\Restaurant\Models\Restaurant;
use Modules\Restaurant\Services\OnboardingHistoryService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OnboardingResubmissionStatusTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public static function actions(): array
    {
        return [
            ['registration', 'resubmitted'], ['menu', 'resubmitted'], ['banking', 'resubmitted'],
            ['tax', 'resubmitted'], ['kyc', 'resubmitted'],
            ['banking', 'details_updated'], ['tax', 'submitted'], [null, 'status_changed'],
        ];
    }

    #[DataProvider('actions')]
    public function test_only_resubmissions_require_action(?string $step, string $action): void
    {
        $histories = Mockery::mock(OnboardingStatusHistory::class);
        $query = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $history = Mockery::mock(OnboardingStatusHistory::class);
        $histories->shouldReceive('newQuery')->once()->andReturn($query);
        $query->shouldReceive('create')->once()->with(Mockery::on(fn ($data) =>
            $data['step_key'] === $step && $data['action'] === $action))->andReturn($history);
        $restaurant = Mockery::mock(Restaurant::class);
        if ($action === 'resubmitted') {
            $history->shouldReceive('getAttribute')->with('restaurant')->once()->andReturn($restaurant);
            $restaurant->shouldReceive('getAttribute')->with('onboarding_status')->andReturn(OnboardingStatus::APPROVED);
            $restaurant->shouldReceive('update')->once()->with(['onboarding_status' => 'action_required'])->andReturnTrue();
        } else {
            $restaurant->shouldNotReceive('update');
        }
        (new OnboardingHistoryService($histories))->record(12, $step, $action, 'approved', 'submitted');
        $this->addToAssertionCount(1);
    }
}
