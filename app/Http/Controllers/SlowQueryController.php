<?php

namespace App\Http\Controllers;

use App\Models\SlowQueryLog;
use App\Models\SlowQuerySetting;
use Illuminate\Http\Request;

class SlowQueryController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = SlowQueryLog::with('user');

        if (!$user->isSuperAdmin()) {
            $query->where('client_id', $user->client_id);
        }

        $logs = $query->latest('executed_at')->paginate(15)->withQueryString();
        $setting = SlowQuerySetting::firstOrCreate(['id' => 1], ['threshold_ms' => 500]);

        return view('slow-queries.index', compact('logs', 'setting'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'threshold_ms' => ['required', 'integer', 'min:50'],
        ]);

        $setting = SlowQuerySetting::firstOrCreate(['id' => 1]);
        $setting->update([
            'threshold_ms' => $request->threshold_ms,
            'updated_by'   => auth()->id(),
        ]);

        return redirect()->back()->with('success', 'Slow Query Threshold updated successfully.');
    }

    public function destroy(SlowQueryLog $slowQueryLog)
    {
        $slowQueryLog->delete();
        return redirect()->back()->with('success', 'Slow query log deleted.');
    }

    public function clearAll()
    {
        SlowQueryLog::truncate();
        return redirect()->back()->with('success', 'All slow query logs cleared successfully.');
    }
}
