<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Collection;
use App\Models\Source;
use App\Models\User;
use Tests\TestCase;

class WebSourceControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_store(): void
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $this->assertDatabaseCount('sources', 0);
        $response = $this->actingAs($user)
            ->post(route('collections.sources.websearch.store', $collection), [
                'title' => 'Test Title',
                'details' => 'Test Details',
            ]);
        $response->assertSessionHas('flash.banner', 'Web source added successfully');
        $this->assertDatabaseCount('sources', 1);

        $source = Source::first();

        $this->assertNotEmpty($source->meta_data);
        $this->assertEquals('brave', $source->meta_data['driver']);
    }

    public function test_update() {
        $source = Source::factory()->create();

        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('collections.sources.websearch.update',
                [
                    'collection' => $source->collection->id,
                    'source' => $source->id
                ]
            ), [
                'title' => 'Test Title2',
                'details' => 'Test Details2',
            ])
            ->assertSessionHasNoErrors()
            ->assertStatus(302);

        $this->assertEquals($source->refresh()->details, 'Test Details2');
    }
}
