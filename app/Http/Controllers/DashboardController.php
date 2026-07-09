<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * DashboardController
 *
 * Dashboard data is cached to prevent heavy aggregate queries from hitting
 * the database on every page load — critical at 1M+ URL scale.
 *
 * Cache TTLs:
 *   - SuperAdmin view  : 5 minutes (cross-company aggregate, expensive)
 *   - Admin view       : 3 minutes (per-company, moderately heavy)
 *   - Member view      : 2 minutes (per-user, lightweight but still cached)
 *
 * Cache is busted automatically when TTL expires.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $urls = collect();
        $members = collect();

        if ($user->role_id === 1) {
            // ── SuperAdmin: all URLs across all companies ────────────────────
            // Most expensive query — cached 5 min per page.
            $page = request()->get('page', 1);
            $cacheKey = "dashboard:superadmin:page:{$page}";

            $urls = Cache::remember($cacheKey, now()->addMinutes(5), function () {
                return ShortUrl::with('company', 'user')
                    ->latest()
                    ->paginate(5);
            });

        } elseif ($user->role_id === 2) {
            // ── Admin: company URLs + member list ────────────────────────────
            $page = request()->get('page', 1);
            $cacheKey = "dashboard:admin:{$user->company_id}:page:{$page}";
            $urls = Cache::remember($cacheKey, now()->addMinutes(3), function () use ($user) {
                return ShortUrl::where('company_id', $user->company_id)
                    ->whereHas('user', function ($query) use ($user) {
                        // Only see his own URLs OR URLs from Members (role 3)
                        $query->where('id', $user->id)
                            ->orWhere('role_id', 3);
                    })
                    ->with('user')
                    ->latest()
                    ->paginate(5);
            });

            $membersCacheKey = "dashboard:members:{$user->company_id}";
            $members = Cache::remember($membersCacheKey, now()->addMinutes(10), function () use ($user) {
                return User::where('company_id', $user->company_id)
                    ->with('role')
                    ->get();
            });
            // dd($cacheKey,$urls,$membersCacheKey,$members);
        } else {
            // ── Member: own URLs ─────────────────────────────────────────────
            $page = request()->get('page', 1);
            $cacheKey = "dashboard:member:{$user->id}:page:{$page}";

            $urls = Cache::remember($cacheKey, now()->addMinutes(2), function () use ($user) {
                return ShortUrl::where('user_id', $user->id)
                    ->latest()
                    ->paginate(5);
            });
        }

        return view('dashboard', compact('urls', 'members'));
    }
}
