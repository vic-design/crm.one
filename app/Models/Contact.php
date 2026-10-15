<?php

namespace App\Models;

use App\Enums\ContactType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = ['user_profile_id', 'type', 'description', 'value'];

    protected $casts = [
        'type' => ContactType::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }
}
