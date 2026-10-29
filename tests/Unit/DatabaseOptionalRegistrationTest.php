<?php

namespace Tests\Unit;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Modules\EmailNotification\Providers\EmailNotificationServiceProvider;
use Modules\Notifications\Providers\NotificationsServiceProvider;
use Modules\Payments\Processors\CodProcessor;
use Modules\Payments\Services\PaymentsProcessorRegistry;
use Modules\Twilio\Providers\TwilioServiceProvider;
use Modules\WebPushNotification\Providers\WebPushNotificationServiceProvider;
use PDOException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

class DatabaseOptionalRegistrationTest extends TestCase
{
    use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
    private Application $app;
    private Container $originalContainer;
    private array $originalArgv;
    private ?ConnectionResolverInterface $originalResolver;

    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__.'/../../Modules/GeneralSetting/src/Helpers/settinghelpers.php';
        $this->originalArgv = $_SERVER['argv'];
        $this->originalResolver = Model::getConnectionResolver();
        $this->originalContainer = Container::getInstance();
        $this->app = new Application();
        $this->app->instance('config', new Repository(['generalsetting' => ['settings' => ['cache' => false]]]));
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($this->app);
        Schema::swap(Mockery::mock(\Illuminate\Database\Schema\Builder::class));
        Log::swap(Mockery::mock(\Psr\Log\LoggerInterface::class));
        $_SERVER['argv'] = ['artisan', 'list'];
    }

    protected function tearDown(): void
    {
        $_SERVER['argv'] = $this->originalArgv;
        if ($this->originalResolver) {
            Model::setConnectionResolver($this->originalResolver);
        } else {
            Model::unsetConnectionResolver();
        }
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance($this->originalContainer);
        parent::tearDown();
    }

    public static function registrations(): array
    {
        return [
            'email' => [EmailNotificationServiceProvider::class, 'resgisterChannel', 'notification_channels'],
            'sms' => [TwilioServiceProvider::class, 'resgisterChannel', 'notification_channels'],
            'system' => [NotificationsServiceProvider::class, 'registerNotificationChannel', 'notification_channels'],
            'webpush' => [WebPushNotificationServiceProvider::class, 'registerNotificationChannel', 'notification_channels'],
            'payments' => [PaymentsProcessorRegistry::class, 'register', 'payment_methods'],
        ];
    }

    private function register(string $class, string $method): void
    {
        if ($class === PaymentsProcessorRegistry::class) {
            $registry = new PaymentsProcessorRegistry();
            $registry->register('cod', new CodProcessor());
            $this->assertTrue($registry->has('cod'));
        } else {
            (new ReflectionMethod($class, $method))->invoke(new $class($this->app));
        }
    }

    #[DataProvider('registrations')]
    public function test_composer_commands_skip_database_access(string $class, string $method, string $table): void
    {
        Schema::shouldReceive('hasTable')->never();
        foreach (['package:discover', 'vendor:publish'] as $command) {
            $_SERVER['argv'] = ['artisan', $command, '--ansi'];
            $this->register($class, $method);
        }
    }

    #[DataProvider('registrations')]
    public function test_missing_table_is_skipped(string $class, string $method, string $table): void
    {
        Schema::shouldReceive('hasTable')->once()->with($table)->andReturnFalse();
        $this->register($class, $method);
    }

    #[DataProvider('registrations')]
    public function test_schema_connection_failure_does_not_block_boot(string $class, string $method, string $table): void
    {
        Schema::shouldReceive('hasTable')->once()->with($table)->andThrow(new PDOException('Offline'));
        Log::shouldReceive('warning')->once();
        $this->register($class, $method);
    }

    #[DataProvider('registrations')]
    public function test_database_failure_after_table_check_does_not_block_boot(string $class, string $method, string $table): void
    {
        Schema::shouldReceive('hasTable')->once()->with($table)->andReturnTrue();
        $resolver = Mockery::mock(ConnectionResolverInterface::class);
        $resolver->shouldReceive('connection')->andThrow(new PDOException('Connection lost'));
        Model::setConnectionResolver($resolver);
        Log::shouldReceive('warning')->once();
        $this->register($class, $method);
    }

    #[DataProvider('registrations')]
    public function test_unrelated_errors_are_not_suppressed(string $class, string $method, string $table): void
    {
        Schema::shouldReceive('hasTable')->once()->with($table)->andThrow(new RuntimeException('Programming error'));
        $this->expectException(RuntimeException::class);
        $this->register($class, $method);
    }
}
