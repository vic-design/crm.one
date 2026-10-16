<?php

namespace App\Http\Controllers;

use App\Exports\ActivityLogExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Excel;
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

    /**
     * Summary of export
     * @param Request $request
     * @param string $format
     */
    public function export(Request $request, string $format)
    {
        $filters = [
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'type' => $request->input('type'),
        ];

        $filename = 'activity-log-' . date('Y-m-d-H-i-s');

        if ($format === 'pdf') {
            $logs = Activity::with(['causer', 'subject'])->latest();

            if (!empty($filters['from'])) {
                $logs->whereDate('created_at', '>=', $filters['from']);
            }
            if (!empty($filters['to'])) {
                $logs->whereDate('created_at', '<=', $filters['to']);
            }
            if (!empty($filters['type'])) {
                $logs->where('log_name', $filters['type']);
            }

            $logs = $logs->get();

            $pdf = Pdf::loadView('exports.activity-log-pdf', compact('logs', 'filters'));
            return $pdf->download($filename . '.pdf');
        }

        if ($format === 'csv') {
            return Excel::download(new ActivityLogExport($filters), $filename . '.csv', Excel::CSV);
        }

        // XLSX по умолчанию
        return Excel::download(new ActivityLogExport($filters), $filename . '.xlsx');
    }
}
