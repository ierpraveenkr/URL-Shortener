<?php

namespace App\Jobs;

use App\Models\ShortUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * TrackUrlClick
 *
 * Queued job that atomically increments the click counter for a short URL.
 *
 * Why async / queued?
 * - Redirect response returns immediately — the user is never blocked waiting
 *   for a DB write.
 * - At 1M+ URLs and high concurrency, synchronous DB writes on every redirect
 *   would cause write contention and slow down response times significantly.
 * - Redis atomic counter is updated instantly; DB is reconciled asynchronously.
 */
class TrackUrlClick implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $shortCode;

    /**
     * Number of times the job may be attempted.
     * If the queue worker is down, we retry up to 3 times.
     */
    public int $tries = 3;

    /**
     * Delay retries by 10 seconds to avoid thundering herd.
     */
    public int $backoff = 10;

    public function __construct(string $shortCode)
    {
        $this->shortCode = $shortCode;
    }

    public function handle(): void
    {
        try {
            // 1. Increment in Redis immediately (real-time counter, ~0.1ms)
            //    This counter is what the dashboard reads for live click stats.
            $redisKey = "url:clicks:{$this->shortCode}";
            Cache::increment($redisKey);

            // 2. Atomically increment in DB (durable, survives Redis restarts)
            //    Uses SQL: UPDATE short_urls SET click_count = click_count + 1
            //    No race conditions — atomic at DB level.
            ShortUrl::where('short_code', $this->shortCode)
                ->increment('click_count');

        } catch (\Exception $e) {
            Log::error("TrackUrlClick failed for [{$this->shortCode}]: " . $e->getMessage());
            $this->fail($e);
        }
    }
}
