<?php

namespace Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;
use Illuminate\Validation\ValidationException;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Modules\Acl\Services\CustomerAuthService;
use Modules\Notifications\Services\NotificationService;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class CustomerPasswordResetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (new \App\Providers\AppServiceProvider(new Container()))->boot();
        Facade::setFacadeApplication(new Container());
        DB::swap(Mockery::mock(\Illuminate\Database\DatabaseManager::class));
        Hash::swap(Mockery::mock(\Illuminate\Contracts\Hashing\Hasher::class));
        Validator::swap(new Factory(new Translator(new ArrayLoader(), 'en')));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        parent::tearDown();
    }

    public static function invalidCodes(): array
    {
        return [
            'missing' => [null, 'No password reset code found.'],
            'consumed' => [['is_verified' => true], 'already been used or invalidated'],
            'expired' => [['expires_at' => now()->subMinute()], 'has expired'],
            'locked' => [['attempts' => 3], 'Maximum verification attempts exceeded'],
            'mismatch' => [[], 'Invalid password reset code. 2 attempts remaining.'],
            'last attempt' => [['attempts' => 2], 'Maximum verification attempts exceeded'],
        ];
    }

    #[DataProvider('invalidCodes')]
    public function test_rejected_code_does_not_change_password(?array $attributes, string $message): void
    {
        [$service, $customer, $code] = $this->serviceWithCode($attributes);
        $customer->shouldNotReceive('save');
        Hash::shouldReceive('make')->never();

        $isMismatch = $attributes !== null && !($attributes['is_verified'] ?? false)
            && !isset($attributes['expires_at']) && ($attributes['attempts'] ?? 0) < 3;
        if ($isMismatch) {
            $code->shouldReceive('incrementAttempts')->once()->andReturnUsing(function () use ($code) {
                $code->attempts++;
            });
        }

        try {
            $service->resetPasswordByEmail('USER@example.com', 'Newpass@123', '9999');
            $this->fail('Expected an OTP validation error.');
        } catch (ValidationException $exception) {
            $this->assertStringContainsString($message, $exception->errors()['otp'][0]);
            $this->assertSame('old-password', $customer->password);
            if ($isMismatch) {
                $this->assertSame(($attributes['attempts'] ?? 0) + 1, $code->attempts);
            }
        }
    }

    public function test_matching_code_updates_password_and_consumes_code(): void
    {
        [$service, $customer, $code, $notifications] = $this->serviceWithCode([]);
        Hash::shouldReceive('make')->once()->with('Newpass@123')->andReturn('hashed-password');
        $customer->shouldReceive('save')->once()->andReturnTrue();
        $code->shouldReceive('markAsVerified')->once();
        $notifications->shouldReceive('trigger')->once()
            ->with('Acl', 'CustomerPasswordReset', 'user@example.com', $customer)->andReturnTrue();

        $result = $service->resetPasswordByEmail('USER@example.com', 'Newpass@123', '1234');

        $this->assertTrue($result['success']);
        $this->assertSame('hashed-password', $customer->password);
    }

    private function serviceWithCode(?array $attributes): array
    {
        $customers = Mockery::mock('alias:Modules\\Acl\\Models\\Customer');
        $customer = Mockery::mock();
        $customer->email = 'user@example.com';
        $customer->password = 'old-password';
        $customers->shouldReceive('whereRaw')->once()
            ->with('LOWER(email) = ?', ['user@example.com'])->andReturnSelf();
        $customers->shouldReceive('first')->once()->andReturn($customer);

        $code = null;
        if ($attributes !== null) {
            $code = Mockery::mock();
            foreach (array_merge([
                'otp' => '1234', 'is_verified' => false, 'attempts' => 0,
                'expires_at' => now()->addMinutes(5),
            ], $attributes) as $key => $value) {
                $code->$key = $value;
            }
        }

        $codes = Mockery::mock('alias:Modules\\Acl\\Models\\CustomerOtp');
        $codes->shouldReceive('query')->once()->andReturnSelf();
        $codes->shouldReceive('where')->once()->with('email', 'user@example.com')->andReturnSelf();
        $codes->shouldReceive('where')->once()->with('type', 'forget_password')->andReturnSelf();
        $codes->shouldReceive('where')->once()->with('channel_type', 'email')->andReturnSelf();
        $codes->shouldReceive('orderByDesc')->once()->with('id')->andReturnSelf();
        $codes->shouldReceive('lockForUpdate')->once()->andReturnSelf();
        $codes->shouldReceive('first')->once()->andReturn($code);

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());
        $notifications = Mockery::mock(NotificationService::class);

        return [new CustomerAuthService($notifications), $customer, $code, $notifications];
    }
}
