<?php

namespace Tests\Feature;

use App\Models\FundManager;
use App\Models\Fund;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundManagerControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_fund_manager()
    {
        $response = $this->postJson('/api/fund-managers', [
            'name' => 'Test Fund Manager',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
            'id',
            'name',
            'created_at',
            'updated_at',
        ]);

        $this->assertDatabaseHas('fund_managers', [
            'name' => 'Test Fund Manager',
        ]);
    }

    /** @test */
    public function it_validates_required_name_on_create()
    {
        $response = $this->postJson('/api/fund-managers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_validates_unique_name_on_create()
    {
        FundManager::factory()->create(['name' => 'Existing Manager']);

        $response = $this->postJson('/api/fund-managers', [
            'name' => 'Existing Manager',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test */
    public function it_can_list_all_fund_managers()
    {
        FundManager::factory()->count(3)->create();

        $response = $this->getJson('/api/fund-managers');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function it_excludes_soft_deleted_fund_managers_from_list()
    {
        FundManager::factory()->create();
        FundManager::factory()->create(['deleted_at' => now()]);

        $response = $this->getJson('/api/fund-managers');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    /** @test */
    public function it_can_soft_delete_a_fund_manager()
    {
        $fundManager = FundManager::factory()->create();

        $response = $this->deleteJson('/api/fund-managers/' . $fundManager->id);

        $response->assertStatus(204);

        $this->assertSoftDeleted('fund_managers', ['id' => $fundManager->id]);
    }

    /** @test */
    public function it_returns_404_when_deleting_non_existent_fund_manager()
    {
        $response = $this->deleteJson('/api/fund-managers/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_preserves_fund_associations_on_soft_delete()
    {
        $fundManager = FundManager::factory()->create();
        $fund = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);

        $this->deleteJson('/api/fund-managers/' . $fundManager->id);

        $this->assertDatabaseHas('funds', [
            'id' => $fund->id,
            'fund_manager_id' => $fundManager->id,
        ]);
    }
}
