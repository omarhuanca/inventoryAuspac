<?php

namespace Tests\Feature;

use App\Modules\Measure\Service\MeasureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MeasureControllerTest extends TestCase
{
    use RefreshDatabase;

    private MeasureService $measureService;

    public function setUp(): void
    {
        parent::setUp();
        $this->measureService = app(MeasureService::class);
    }

    public function test_can_list_measures()
    {
        $this->measureService->createMeasure(['code' => 'KG']);
        $this->measureService->createMeasure(['code' => 'GR']);

        $response = $this->getJson('/api/measures');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Measure list.',
                'error' => false,
            ])
            ->assertJsonCount(2, 'data');
    }

    public function test_can_create_a_measure()
    {
        $data = ['code' => 'KG'];

        $response = $this->postJson('/api/measures', $data);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Measure created.',
                'statusCode' => 201,
                'error' => false,
            ]);

        $this->assertDatabaseHas('measure', ['code' => 'KG']);
    }

    public function test_code_must_be_required()
    {
        $data = ['code' => ''];

        $response = $this->postJson('/api/measures', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field is required.']]);
    }

    public function test_code_cannot_contain_numbers()
    {
        $data = ['code' => 'A123'];

        $response = $this->postJson('/api/measures', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field format is invalid.']]);
    }

    public function test_code_cannot_exceed_maximum_length()
    {
        $data = ['code' => 'TOOLONGCODE'];

        $response = $this->postJson('/api/measures', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field must not be greater than 10 characters.']]);
    }

    public function test_code_cannot_contain_special_characters()
    {
        $data = ['code' => 'kg!'];

        $response = $this->postJson('/api/measures', $data);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'statusCode', 'error', 'data']);
        $response->assertJsonFragment(['data' => ['The code field format is invalid.']]);
    }

    public function test_cannot_create_a_measure_with_duplicate_code()
    {
        $this->measureService->createMeasure(['code' => 'KG']);

        $response = $this->postJson('/api/measures', ['code' => 'KG']);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Error when creating the measure: Measure code already exists.',
                'error' => true,
            ]);

        $this->assertDatabaseCount('measure', 1);
    }

    public function test_can_show_a_measure_by_id()
    {
        $measure = $this->measureService->createMeasure(['code' => 'UNIT']);

        $response = $this->getJson("/api/measures/{$measure->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Measure found',
                'error' => false,
            ])
            ->assertJsonPath('data.code', 'UNIT');
        $this->assertDatabaseHas('measure', ['code' => 'UNIT',]);
    }

    public function test_returns_404_when_showing_non_existent_measure()
    {
        $response = $this->getJson('/api/measures/99');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Measure not found.',
                'error' => true,
            ]);
    }

    public function test_can_update_a_measure()
    {
        $measure = $this->measureService->createMeasure(['code' => 'KG']);

        $updateData = ['code' => 'LT'];

        $response = $this->putJson("/api/measures/{$measure->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Measure updated.',
                'statusCode' => 200,
                'error' => false,
            ]);

        $this->assertDatabaseHas('measure', ['code' => 'LT']);
    }

    public function test_cannot_update_to_duplicate_code()
    {
        $this->measureService->createMeasure(['code' => 'KG']);
        $second = $this->measureService->createMeasure(['code' => 'LT']);

        $response = $this->putJson("/api/measures/{$second->id}", ['code' => 'KG']);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Error when updating the measure: Measure code already exists.',
                'statusCode' => 422,
                'error' => true,
            ]);

        $this->assertDatabaseHas('measure', ['code' => 'LT']);
        $this->assertDatabaseCount('measure', 2);
    }

    public function test_returns_404_when_updating_non_existent_measure()
    {
        $response = $this->putJson('/api/measures/99', ['code' => 'LT']);

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Measure not found.',
                'error' => true,
            ]);
    }
}
