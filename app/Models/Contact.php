<?php

namespace App\Models;

use App\Enums\ContactType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Activity;
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
     * Summary of profile
     * @return BelongsTo<UserProfile, Contact>
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    /**
     * Summary of getActivitylogOptions
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['type', 'value', 'description'])
            ->useLogName('contacts');
    }

    /**
     * Summary of getDescriptionForEvent
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        $typeLabel = $this->type->label();
        $action = $this->getActionLabel($eventName, lowercase: true);

        return "Контакт '{$typeLabel}' ({$this->value}) был {$action}";
    }

    /**
     * Summary of tapActivity
     * @param Activity $activity
     * @param string $eventName
     * @return void
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        $profileOwnerName = $this->profile?->user?->name ?? 'Неизвестно';

        $this->logToFile(
            'contacts',
            'Изменение контактов',
            "Контакт у '{$profileOwnerName}'",
            $activity->description
        );
    }

}
