<?php

namespace Tests\Feature;

use App\Models\DuplicateWarning;
use App\Models\Fund;
use App\Models\FundManager;
use App\Models\Company;
use App\Models\Alias;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuplicateWarningControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_unresolved_duplicate_warnings()
    {
        // Create fund manager and funds
        $fundManager = FundManager::factory()->create();
        $fund1 = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);
        $fund2 = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);

        // Create unresolved warning
        DuplicateWarning::factory()->create([
            'fund_id_1' => $fund1->id,
            'fund_id_2' => $fund2->id,
            'resolved' => false,
        ]);

        $response = $this->getJson('/api/duplicate-warnings');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonStructure([
            '*' => [
                'id',
                'fund_id_1',
                'fund_id_2',
                'resolved',
                'created_at',
                'updated_at',
                'fund1',
                'fund2',
            ]
        ]);
    }

    /** @test */
    public function it_excludes_resolved_warnings_from_list()
    {
        // Create fund manager and funds
        $fundManager = FundManager::factory()->create();
        $fund1 = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);
        $fund2 = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);
        $fund3 = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);

        // Create unresolved warning
        DuplicateWarning::factory()->create([
            'fund_id_1' => $fund1->id,
            'fund_id_2' => $fund2->id,
            'resolved' => false,
        ]);

        // Create resolved warning
        DuplicateWarning::factory()->create([
            'fund_id_1' => $fund1->id,
            'fund_id_2' => $fund3->id,
            'resolved' => true,
        ]);

        $response = $this->getJson('/api/duplicate-warnings');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    /** @test */
    public function it_includes_fund_details_in_warnings()
    {
        // Create fund manager and funds with aliases and companies
        $fundManager = FundManager::factory()->create(['name' => 'Test Manager']);
        $company = Company::factory()->create(['name' => 'Test Company']);

        $fund1 = Fund::factory()->create([
            'name' => 'Fund One',
            'fund_manager_id' => $fundManager->id
        ]);
        $fund1->companies()->attach($company->id);
        Alias::factory()->create(['fund_id' => $fund1->id, 'name' => 'Alias One']);

        $fund2 = Fund::factory()->create([
            'name' => 'Fund Two',
            'fund_manager_id' => $fundManager->id
        ]);

        // Create warning
        DuplicateWarning::factory()->create([
            'fund_id_1' => $fund1->id,
            'fund_id_2' => $fund2->id,
            'resolved' => false,
        ]);

        $response = $this->getJson('/api/duplicate-warnings');

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Fund One'])
            ->assertJsonFragment(['name' => 'Fund Two'])
            ->assertJsonFragment(['name' => 'Test Manager'])
            ->assertJsonFragment(['name' => 'Alias One'])
            ->assertJsonFragment(['name' => 'Test Company']);
    }

    /** @test */
    public function it_returns_empty_array_when_no_unresolved_warnings()
    {
        $response = $this->getJson('/api/duplicate-warnings');

        $response->assertStatus(200)
            ->assertJsonCount(0);
    }
}
