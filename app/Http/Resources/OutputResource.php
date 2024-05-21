<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OutputResource extends JsonResource
{
    use HelperTrait;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $recurring = $this->getRecurring();
        $lastRun = $this->getLastRun();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type->value,
            'type_formatted' => str($this->type->name)->headline()->toString(),
            'collection_id' => $this->collection_id,
            'summary' => $this->summary,
            'summary_truncated' => str($this->summary)->limit(128)->markdown(),
            'active' => $this->active,
            'public' => $this->public,
            'recurring' => $recurring,
            'last_run' => $lastRun,
            'slug' => $this->slug,
            'url' => route('collections.outputs.web_page.show', [
                'output' => $this->slug,
            ]),
        ];
    }
}
