<?php

namespace Modules\Blogs\Console;

use Illuminate\Console\Command;
use Modules\Blogs\Jobs\PublishScheduledBlogsJob;

class PublishScheduledBlogsCommand extends Command
{
    protected $signature = 'blogs:publish-scheduled-blogs';

    protected $description = 'Publish blogs whose scheduled_at time has passed';

    public function handle(): int
    {
        $this->info('Checking for due scheduled blogs...');

        // Run synchronously so `php artisan schedule:work` publishes without a queue worker
        (new PublishScheduledBlogsJob())->handle();

        $this->info('Done.');

        return self::SUCCESS;
    }
}
