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
        $existingContacts = $this->contacts->keyBy(function ($contact) {
            return $contact->type->value . '|' . $contact->value;
        });

        $newContacts = collect($contactsData)
            ->filter(fn($c) => !empty($c['type']) && !empty($c['value']))
            ->keyBy(fn($c) => $c['type'] . '|' . $c['value']);

        foreach ($existingContacts as $key => $contact) {
            if (!$newContacts->has($key)) {
                $contact->delete();
            }
        }

        foreach ($newContacts as $key => $contactData) {
            if ($existingContacts->has($key)) {
                // Контакт существует — проверяем, нужно ли обновить description
                $existing = $existingContacts->get($key);
                if ($existing->description !== ($contactData['description'] ?? null)) {
                    $existing->update([
                        'description' => $contactData['description'] ?? null,
                    ]);
                }
            } else {
                $this->contacts()->create([
                    'type' => $contactData['type'],
                    'value' => $contactData['value'],
                    'description' => $contactData['description'] ?? null,
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
            \Illuminate\Support\Facades\Log::info('DEBUG: UserProfile tapActivity вызван для ' . $eventName);

        $profileOwnerName = $this->user?->name ?? 'Неизвестно';
        $action = $this->getActionLabel($eventName);

        $this->logToFile(
            'Изменение профиля',
            $this->getCurrentUserName(),
            "Профиль пользователя '{$profileOwnerName}' ({$action})",
            $eventName === 'updated' ? 'Обновлены комментарии/био' : $action
        );
    }

}
