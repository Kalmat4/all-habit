<?php

namespace App\Services;

use App\Models\Impulse;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function weeklyStats(int $userId, Carbon $from, Carbon $to): Collection
    {
        return Impulse::query()
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('dependency_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(resisted) as resisted')
            ->selectRaw('ROUND(SUM(resisted) / COUNT(*) * 100) as rate')
            ->groupBy('dependency_id')
            ->get()
            ->keyBy('dependency_id');
    }

    public function periodStats(int $userId, ?Carbon $from = null): Collection
    {
        $q = Impulse::query()
            ->where('user_id', $userId)
            ->selectRaw('dependency_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(resisted) as resisted')
            ->selectRaw('ROUND(SUM(resisted) / COUNT(*) * 100) as rate');

        if ($from) {
            $q->where('created_at', '>=', $from);
        }

        return $q->groupBy('dependency_id')->get()->keyBy('dependency_id');
    }

    public function weeklyTrend(int $userId, int $weeks = 4): Collection
    {
        $from = now()->startOfWeek()->subWeeks($weeks - 1);

        return Impulse::query()
            ->where('user_id', $userId)
            ->where('created_at', '>=', $from)
            ->selectRaw('YEARWEEK(created_at, 3) as yw')
            ->selectRaw('DATE(created_at - INTERVAL (WEEKDAY(created_at)) DAY) as week_start')
            ->selectRaw('DATE(created_at - INTERVAL (WEEKDAY(created_at)) DAY + INTERVAL 6 DAY) as week_end')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('ROUND(SUM(resisted) / COUNT(*) * 100) as rate')
            ->groupBy('yw')
            ->orderBy('yw')
            ->get();
    }

    public function topTriggers(int $userId, int $depId, int $limit = 5): Collection
    {
        return Impulse::query()
            ->where('user_id', $userId)
            ->where('dependency_id', $depId)
            ->whereNotNull('trigger')
            ->where('trigger', '!=', '')
            ->selectRaw('LOWER(`trigger`) as name, COUNT(*) as count')
            ->groupByRaw('LOWER(`trigger`)')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }

    public function topTriggersAll(int $userId): Collection
    {
        return Impulse::query()
            ->where('user_id', $userId)
            ->whereNotNull('trigger')->where('trigger', '!=', '')
            ->selectRaw('dependency_id, LOWER(`trigger`) as name, COUNT(*) as count')
            ->groupByRaw('dependency_id, LOWER(`trigger`)')
            ->orderByDesc('count')
            ->get()
            ->groupBy('dependency_id')          // -> { depId: [...] }
            ->map(fn ($g) => $g->take(5)->values());
    }
}
