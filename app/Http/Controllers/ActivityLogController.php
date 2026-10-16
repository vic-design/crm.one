<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Summary of index
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $type = $request->input('type');

        $query = Activity::with(['causer', 'subject'])->latest();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }
        if ($type) {
            $query->where('log_name', $type);
        }

        $logs = $query->paginate(25)->withQueryString();

        $selectedLog = null;
        if ($request->has('log_id')) {
            $selectedLog = Activity::with(['causer', 'subject'])->find($request->input('log_id'));
        }

        $availableTypes = Activity::distinct()->pluck('log_name')->toArray();

        return Inertia::render('ActivityLog/Index', [
            'logs' => $logs,
            'selectedLog' => $selectedLog,
            'filters' => [
                'from' => $from,
                'to' => $to,
            ],
            'availableTypes' => $availableTypes,
        ]);
    }
}
