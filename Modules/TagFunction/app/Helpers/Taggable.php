<?php 

namespace LlmLaraHub\TagFunction\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LlmLaraHub\TagFunction\Models\Tag;

trait Taggable {

    public function tags() : MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function addTag(string $tag) : void
    {
        $tag = Tag::firstOrCreate(['name' => $tag]);
        $this->tags()->attach($tag->id);
    }
}

