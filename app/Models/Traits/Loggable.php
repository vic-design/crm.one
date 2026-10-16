<?php

namespace App\Models\Traits;

trait Loggable
{
    protected function getActionLabel(string $eventName): string
    {
        return match($eventName) {
            'created' => 'создан',
            'updated' => 'обновлён',
            'deleted' => 'удалён',
            default => $eventName,
        };
    }
}
