<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait Loggable
{
    /**
     * Получить человекочитаемое действие из имени события
     *
     * @param string $eventName Имя события (created/updated/deleted)
     * @param bool $lowercase Вернуть в нижнем регистре (для использования в предложениях)
     */
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

    /**
     * Получить имя текущего пользователя (или "Система")
     */
    protected function getCurrentUserName(): string
    {
        return Auth::user()?->name ?? 'Система';
    }

    /**
     * Записать событие в файл activity.log
     */
    protected function logToFile(string $channel, string $message, string $action, string $details): void
    {
        Log::channel('activity_file')->info($message, [
            'user' => $this->getCurrentUserName(),
            'action' => $action,
            'details' => $details,
        ]);
    }
}
