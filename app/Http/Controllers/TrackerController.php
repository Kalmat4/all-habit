<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImpulseRequest;
use App\Models\Dependency;
use App\Models\Impulse;
use App\Services\ReportService;
use Illuminate\Http\Request;

class TrackerController extends Controller
{
    public function index(ReportService $report)
    {
        $userId = auth()->id();

        return inertia('Tracker/Index', [
            'dependencies' => Dependency::where('user_id', $userId)
                ->where('is_active', true)->get(),
            'todayEntries' => Impulse::with('dependency')
                ->where('user_id', $userId)
                ->whereDate('created_at', today())
                ->latest()->get(),
            'report7d'  => $report->periodStats($userId, now()->subDays(7)),
            'report30d' => $report->periodStats($userId, now()->subDays(30)),
            'reportAll' => $report->periodStats($userId),
            'trend'     => $report->weeklyTrend($userId, 4),
            'topTriggers' => $report->topTriggersAll($userId),
        ]);
    }

    public function impulses(Request $request)
    {
        $request->validate([
            'dependency_id' => 'required|integer',
            'period'        => 'required|in:7d,30d,all',
        ]);

        $q = Impulse::where('user_id', auth()->id())
            ->where('dependency_id', $request->dependency_id)
            ->latest();

        if ($request->period === '7d') {
            $q->where('created_at', '>=', now()->subDays(7));
        } elseif ($request->period === '30d') {
            $q->where('created_at', '>=', now()->subDays(30));
        }

        return $q->paginate(15);
    }

    public function store(StoreImpulseRequest $request)
    {
        auth()->user()->impulses()->create($request->validated());

        return back();
    }

    public function destroy(Impulse $impulse)
    {
        abort_unless($impulse->user_id === auth()->id(), 403);
        $impulse->delete();

        return back();
    }
}
