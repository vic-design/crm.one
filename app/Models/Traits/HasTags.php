<?php

namespace App\Models\Traits;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Str;

trait HasTags
{
    /**
     * Summary of tags
     * @return MorphToMany
     */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Summary of syncTags
     * @param array $tagIds
     * @return void
     */
    public function syncTags(array $tagIds): void
    {
        $this->tags()->sync($tagIds);
    }

    /**
     * Summary of attachTagsByNames
     * @param array $tagNames
     * @return void
     */
    public function attachTagsByNames(array $tagNames): void
    {
        $tagIds = collect($tagNames)->map(function ($name) {
            return Tag::firstOrCreate(
                ['name' => $name],
                ['slug' => Str::slug($name)]
            )->id;
        })->toArray();

        $this->tags()->sync($tagIds);
    }
}
