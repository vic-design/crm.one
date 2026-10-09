<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'color'];

    /**
     * Summary of booted
     * @return void
     */
    protected static function booted()
    {
        static::creating(function (Tag $tag) {
            if(empty($tag->slug)) {
                $tag->slug = Str::slug($tag->name);
            }
        });

        static::updating(function (Tag $tag) {
            if($tag->isDirty('name')) {
                $tag->slug = Str::slug($tag->name);
            }
        });
    }

    /**
     * Summary of users
     * @return MorphToMany<User, Tag>
     */
    public function users(): MorphToMany
    {
        return $this->morphToMany(User::class, 'taggable');
    }
}
