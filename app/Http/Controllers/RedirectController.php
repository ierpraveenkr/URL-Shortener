<?php

namespace App\Http\Controllers;

use App\Jobs\TrackUrlClick;
use App\Models\ShortUrl;
use Illuminate\Http\Request;

/**
 * RedirectController
 *
 * Handles the core short URL redirect flow with a multi-layer caching strategy
 * designed to handle 1,000,000+ URLs efficiently.
 *
 * Flow:
 *   Request → Rate Limiter (60/min per IP)
 *           → [L1] Redis Cache lookup by short_code (~0.1ms on hit)
 *           → [L2] DB fallback on cache miss (~5ms, result cached 24h)
 *           → Dispatch async click tracking job
 *           → 302 Redirect to original URL
 */
class RedirectController extends Controller
{
    public function redirect(Request $request, string $short_code)
    {
        // ── L1 / L2: Cache-Aside lookup ──────────────────────────────────────
        // resolveFromCode() checks Redis first. On miss, queries DB and writes
        // the result back to Redis with a 24-hour TTL automatically.
        $url = ShortUrl::resolveFromCode($short_code);

        if (!$url) {
            abort(404, 'Short URL not found or has expired.');
        }

        // ── Async click tracking ──────────────────────────────────────────────
        // Dispatches a queued job — redirect response is NOT blocked.
        // The job atomically increments both the Redis counter and the DB column.
        TrackUrlClick::dispatch($short_code);

        // ── Redirect ──────────────────────────────────────────────────────────
        return redirect()->away($url->original_url, 302);
    }
}
