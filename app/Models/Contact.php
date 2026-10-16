<?php

namespace App\Models;

use App\Enums\ContactType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Contact extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['user_profile_id', 'type', 'description', 'value'];

    protected $casts = [
        'type' => ContactType::class,
    ];

    /**
     * Настройки логирования для Spatie
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'value', 'description'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(function (string $eventName) {
                $typeLabel = $this->type->label();

                $action = match($eventName) {
                    'created' => 'добавлен',
                    'updated' => 'изменён',
                    'deleted' => 'удалён',
                    default => $eventName,
                };

                return "Контакт '{$typeLabel}' ({$this->value}) был {$action}";
            });
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }
}
