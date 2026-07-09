<?php

namespace App\Console\Commands;

use App\Models\ShortUrl;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * WarmUrlCache
 *
 * Pre-loads the most frequently clicked short URLs into Redis cache on startup
 * or on a schedule. This eliminates cold misses for your hottest URLs.
 *
 * Usage:
 *   php artisan url:warm-cache           # warm top 1000 URLs (default)
 *   php artisan url:warm-cache --limit=5000   # warm top 5000 URLs
 *
 * Schedule: runs daily at 3 AM via Console/Kernel.php
 *
 * Strategy:
 *   - Fetches top N URLs ordered by click_count DESC
 *   - Writes each to Redis with 24h TTL
 *   - Uses chunk() to avoid loading 1M records into memory at once
 */
class WarmUrlCache extends Command
{
    protected $signature = 'url:warm-cache
                            {--limit=1000 : Number of top URLs to warm}';

    protected $description = 'Pre-warm Redis cache with the most popular short URLs for near-zero latency redirects';

    public function handle(): int
    {
        $limit = (int) $this->option('limit');

        $this->info("🔥 Warming Redis cache with top {$limit} most-clicked URLs...");
        $bar = $this->output->createProgressBar($limit);
        $bar->start();

        $warmed = 0;

        ShortUrl::active()
            ->orderByDesc('click_count')
            ->limit($limit)
            ->chunk(200, function ($urls) use ($bar, &$warmed) {
                foreach ($urls as $url) {
                    // Write to Redis with 24h TTL (same as Cache-Aside TTL)
                    Cache::put("url:{$url->short_code}", $url, now()->addHours(24));
                    $warmed++;
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->newLine();
        $this->info("✅ Cache warmed: {$warmed} URLs loaded into Redis.");
        $this->line("   → Next cache expiry: " . now()->addHours(24)->toDateTimeString());

        return self::SUCCESS;
    }
}
