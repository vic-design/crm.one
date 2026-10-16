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
        $logs = Activity::with(['causer', 'subject'])
            ->latest()
            ->paginate(25);

        $selectedLog = null;
        if ($request->has('log_id')) {
            $selectedLog = Activity::with(['causer', 'subject'])->find($request->input('log_id'));
        }


        return Inertia::render('ActivityLog/Index', [
            'logs' => $logs,
            'selectedLog' => $selectedLog,
        ]);
    }
}
