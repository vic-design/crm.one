<?php

namespace App\Http\Controllers;

use App\Exports\ActivityLogExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Summary of index
     * @param Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request): Response
    {
        $from = $request->input('from');
        $to = $request->input('to');
        $type = $request->input('type');

        $perPage = (int) $request->input('per_page', config('pagination.default_per_page'));
        $allowedPerPage = config('pagination.per_page_options');
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = config('pagination.default_per_page');
        }

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

        $logs = $query->paginate($perPage)->withQueryString();

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
                'type' => $type,
                'per_page' => $perPage,
            ],
            'availableTypes' => Activity::distinct()->pluck('log_name')->toArray(),
            'perPageOptions' => $allowedPerPage,
        ]);
    }

    /**
     * Summary of export
     * @param Request $request
     * @param string $format
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
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
            return Excel::download(new ActivityLogExport($filters), $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new ActivityLogExport($filters), $filename . '.xlsx');
    }
}
