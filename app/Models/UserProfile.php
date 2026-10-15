<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'bio'];

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
        return $this->hasMany(Contact::class, 'user_id', 'user_id');
    }

    /**
     * Удобный метод для синхронизации контактов
     */
    public function syncContacts(array $contactsData): void
    {
        $this->contacts()->delete();

        foreach ($contactsData as $contact) {
            if (!empty($contact['type']) && !empty($contact['value'])) {
                $this->contacts()->create([
                    'type' => $contact['type'],
                    'value' => $contact['value'],
                ]);
            }
        }
    }
}
