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

        $query = Activity::with(['causer', 'subject'])->latest();

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(25)->withQueryString();

        $selectedLog = null;
        if ($request->has('log_id')) {
            $selectedLog = Activity::with(['causer', 'subject'])->find($request->input('log_id'));
        }

        return Inertia::render('ActivityLog/Index', [
            'logs' => $logs,
            'selectedLog' => $selectedLog,
            'filters' => [
                'from' => $from,
                'to' => $to,
            ],
        ]);
    }
}
