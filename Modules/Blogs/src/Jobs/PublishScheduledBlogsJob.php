<?php

namespace Modules\Blogs\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Blogs\Models\Blog;
use Modules\Blogs\Services\BlogService;

class PublishScheduledBlogsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function handle(): void
    {
        $nowUtc = now('UTC');

        $blogs = Blog::query()
            ->with('author:id,slug')
            ->where('status', 'scheduled')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', $nowUtc)
            ->get();

        if ($blogs->isEmpty()) {
            return;
        }

        foreach ($blogs as $blog) {
            try {
                $blog->update([
                    'status' => 'published',
                    'published_at' => $blog->published_at ?? $blog->scheduled_at ?? now(),
                ]);

                BlogService::invalidateBlogCaches(
                    (string) $blog->post_url,
                    $blog->author?->slug
                );
            } catch (\Throwable $e) {
                Log::error('Failed to publish scheduled blog', [
                    'blog_id' => $blog->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}

