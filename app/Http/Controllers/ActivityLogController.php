<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    /**
     * Summary of index
     * @return \Inertia\Response
     */
    public function index(): Response
    {
        // Загружаем логи с информацией о том, КТО сделал (causer) и ЧТО изменил (subject)
        $logs = Activity::with(['causer', 'subject'])
            ->latest()
            ->paginate(25);

        return Inertia::render('ActivityLog/Index', [
            'logs' => $logs,
        ]);
    }
}
