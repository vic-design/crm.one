<?php

namespace App\Models;

use App\Enums\ContactType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class UserProfile extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['user_id', 'bio'];

    protected $appends = ['primary_phone', 'primary_telegram'];

    /**
     * Summary of user
     * @return BelongsTo<User, UserProfile>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /**
     * Summary of syncContacts
     * @param array $contactsData
     * @return void
     */
    public function syncContacts(array $contactsData): void
    {
        $this->contacts()->delete();

        foreach ($contactsData as $contact) {
            if (!empty($contact['type']) && !empty($contact['value'])) {
                $this->contacts()->create([
                    'type' => $contact['type'],
                    'value' => $contact['value'],
                    'description' => $contact['description'] ?? null,
                ]);
            }
        }
    }

    /**
     * Summary of getPrimaryPhoneAttribute
     */
    public function getPrimaryPhoneAttribute(): ?string
    {
        return $this->contacts
            ->where('type', ContactType::PHONE)
            ->first()
            ?->value;
    }

    /**
     * Summary of getPrimaryTelegramAttribute
     */
    public function getPrimaryTelegramAttribute(): ?string
    {
        return $this->contacts
            ->where('type', ContactType::TELEGRAM)
            ->first()
            ?->value;
    }

    /**
     * Summary of getActivitylogOptions
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['bio'])
            ->setDescriptionForEvent(fn(string $eventName) => "Профиль пользователя был {$eventName}")
            ->useLogName('profiles');
    }

    /**
     * Summary of tapActivity
     * @param Activity $activity
     * @param string $eventName
     * @return void
     */
    public function tapActivity(Activity $activity, string $eventName): void
    {
        $profileOwnerName = $this->user?->name ?? 'Неизвестно';
        $action = $this->getActionLabel($eventName);

        $this->logToFile(
            'profiles',
            'Изменение профиля',
            "Профиль пользователя '{$profileOwnerName}' ({$action})",
            $eventName === 'updated' ? 'Обновлены комментарии/био' : $action
        );
    }

}
