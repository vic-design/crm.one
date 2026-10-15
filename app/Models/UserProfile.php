<?php

namespace App\Models;

use App\Enums\ContactType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory, SoftDeletes;

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

}
