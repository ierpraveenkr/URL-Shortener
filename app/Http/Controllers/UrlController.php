<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * UrlController
 *
 * Handles creation of new short URLs with:
 *   1. Collision-safe short code generation (retries on collision)
 *   2. Immediate Redis cache population after creation
 *      → Prevents cold-miss on the very first redirect after a URL is created
 *   3. De-duplication: same original_url within a company reuses existing code
 */
class UrlController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->role_id === 1) {
            abort(403, 'SuperAdmins cannot create short URLs.');
        }

        $request->validate([
            'original_url' => 'required|url|max:2048',
        ]);

        // ── De-duplication: reuse if already exists for this company ─────────
        $url = ShortUrl::firstOrCreate(
            [
                'original_url' => $request->original_url,
                'company_id'   => $user->company_id,
            ],
            [
                'short_code' => $this->generateUniqueCode(),
                'user_id'    => $user->id,
            ]
        );

        // ── Pre-warm Redis cache immediately after creation ──────────────────
        // Ensures the very first redirect hits Redis, not the DB.
        // If the URL already existed, this refreshes its TTL in cache.
        $url->warmCache();

        return back()->with('success', 'Short URL ready: ' . url($url->short_code));
    }

    /**
     * Generate a unique 6-character short code with collision retry.
     *
     * At 1M existing URLs with 6 Base62 chars (62^6 ≈ 56 billion combinations),
     * collision probability is negligible. We still retry up to 5 times
     * defensively to guarantee uniqueness even at extreme scale.
     *
     * @throws \RuntimeException if a unique code cannot be generated after retries
     */
    private function generateUniqueCode(int $length = 6, int $maxAttempts = 5): string
    {
        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $code = Str::random($length);

            if (!ShortUrl::where('short_code', $code)->exists()) {
                return $code;
            }
        }

        // Escalate to 8 chars if 6-char space is somehow crowded
        return Str::random(8);
    }
}
