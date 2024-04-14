<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LlmLaraHub\LlmDriver\DriversEnum;
use LlmLaraHub\LlmDriver\HasDrivers;
use LlmLaraHub\TagFunction\Helpers\Taggable;

/**
 * Class Project
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property DriversEnum $driver
 * @property DriversEnum $embedding_driver
 * @property bool $active
 * @property int $team_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Collection extends Model implements HasDrivers
{
    use HasFactory;
    use Taggable;

    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
        'driver' => DriversEnum::class,
        'embedding_driver' => DriversEnum::class,
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getDriver(): string
    {
        return $this->driver->value;
    }

    public function getSummary(): string
    {
        return $this->description;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return Collection::class;
    }

    public function getEmbeddingDriver(): string
    {
        return $this->embedding_driver->value;
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function chats(): MorphMany
    {
        return $this->morphMany(Chat::class, 'chatable');
    }

    public function systemPrompt(): string
    {
        $systemPrompt = config('llmlarahub.collection.system_prompt');
        $prompt = <<<EOD
{$systemPrompt}: 
{$this->description}
EOD;

        return $prompt;
    }
}
