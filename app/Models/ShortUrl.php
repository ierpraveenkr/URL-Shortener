<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ShortUrl extends Model
{
    use HasFactory;

    protected $fillable = [
        'short_code',
        'original_url',
        'user_id',
        'company_id',
        'click_count',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'click_count' => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Only return URLs that have not expired.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    // -------------------------------------------------------------------------
    // Cache-Aside Helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve a short code to its original URL using a Cache-Aside strategy.
     *
     * Flow:
     *   1. Check Redis for cached URL data (key: url:{short_code})
     *   2. On cache miss  → query DB, write result back to Redis (24h TTL)
     *   3. On cache hit   → return immediately (~0.1ms, no DB touch)
     *
     * @param  string $code
     * @return self|null
     */
    public static function resolveFromCode(string $code): ?self
    {
        $cacheKey = "url:{$code}";

        $data = Cache::remember($cacheKey, now()->addHours(24), function () use ($code) {
            return static::active()
                ->where('short_code', $code)
                ->first();
        });

        return $data instanceof self ? $data : null;
    }

    /**
     * Immediately write this URL into the Redis cache after creation.
     * Prevents cold-miss on the very first redirect after a URL is created.
     */
    public function warmCache(): void
    {
        Cache::put("url:{$this->short_code}", $this, now()->addHours(24));
    }

    /**
     * Evict this URL from the Redis cache (e.g., after deletion or expiry).
     */
    public function flushCache(): void
    {
        Cache::forget("url:{$this->short_code}");
    }
}
