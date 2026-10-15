<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait Loggable
{
    protected function getActionLabel(string $eventName, bool $lowercase = false): string
    {
        $label = match($eventName) {
            'created' => 'Создан',
            'updated' => 'Обновлён',
            'deleted' => 'Удалён',
            default => $eventName,
        };

        return $lowercase ? mb_strtolower($label) : $label;
    }

    protected function getCurrentUserName(): string
    {
        return Auth::user()?->name ?? 'Система';
    }

    protected function logToFile(string $message, string $user, string $action, string $details): void
    {
        $logMessage = sprintf(
            "[%s] 👤 %s | 📝 %s | 📦 %s",
            now()->format('Y-m-d H:i:s'),
            $user,
            $action,
            $details
        );

        Log::channel('activity_file')->info($logMessage);
    }
}
