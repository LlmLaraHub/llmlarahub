<?php

namespace Tests\Feature;

use App\Domains\Documents\TypesEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TypesEnumTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_gets_mime_type_to_type(): void
    {
        $results = TypesEnum::mimeTypeToType(
            'application/vnd.openxmlformats-officedocument.presentationml.presentation');


        $this->assertEquals(TypesEnum::Pptx, $results);
    }
}
