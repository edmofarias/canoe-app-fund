<?php

namespace Tests\Feature;

use App\Models\Fund;
use App\Models\FundManager;
use App\Models\Company;
use App\Models\Alias;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_fund_with_basic_data()
    {
        $fundManager = FundManager::factory()->create();

        $response = $this->postJson('/api/funds', [
            'name' => 'Test Fund',
            'start_year' => 2020,
            'fund_manager_id' => $fundManager->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
            'id',
            'name',
            'start_year',
            'fund_manager_id',
            'fund_manager',
            'aliases',
            'companies',
        ]);

        $this->assertDatabaseHas('funds', [
            'name' => 'Test Fund',
            'start_year' => 2020,
            'fund_manager_id' => $fundManager->id,
        ]);
    }

    /** @test */
    public function it_can_create_a_fund_with_aliases_and_companies()
    {
        $fundManager = FundManager::factory()->create();
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();

        $response = $this->postJson('/api/funds', [
            'name' => 'Test Fund',
            'start_year' => 2020,
            'fund_manager_id' => $fundManager->id,
            'aliases' => ['Alias 1', 'Alias 2'],
            'company_ids' => [$company1->id, $company2->id],
        ]);

        $response->assertStatus(201);

        $fund = Fund::first();
        $this->assertCount(2, $fund->aliases);
        $this->assertCount(2, $fund->companies);
    }

    /** @test */
    public function it_validates_required_fields_on_create()
    {
        $response = $this->postJson('/api/funds', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'start_year', 'fund_manager_id']);
    }

    /** @test */
    public function it_can_list_all_funds()
    {
        $fundManager = FundManager::factory()->create();
        Fund::factory()->count(3)->create(['fund_manager_id' => $fundManager->id]);

        $response = $this->getJson('/api/funds');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    /** @test */
    public function it_can_filter_funds_by_name()
    {
        $fundManager = FundManager::factory()->create();
        Fund::factory()->create(['name' => 'Alpha Fund', 'fund_manager_id' => $fundManager->id]);
        Fund::factory()->create(['name' => 'Beta Fund', 'fund_manager_id' => $fundManager->id]);

        $response = $this->getJson('/api/funds?name=Alpha');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['name' => 'Alpha Fund']);
    }

    /** @test */
    public function it_can_filter_funds_by_fund_manager()
    {
        $fundManager1 = FundManager::factory()->create();
        $fundManager2 = FundManager::factory()->create();
        Fund::factory()->count(2)->create(['fund_manager_id' => $fundManager1->id]);
        Fund::factory()->create(['fund_manager_id' => $fundManager2->id]);

        $response = $this->getJson('/api/funds?fund_manager_id=' . $fundManager1->id);

        $response->assertStatus(200)
            ->assertJsonCount(2);
    }

    /** @test */
    public function it_can_filter_funds_by_start_year()
    {
        $fundManager = FundManager::factory()->create();
        Fund::factory()->create(['start_year' => 2020, 'fund_manager_id' => $fundManager->id]);
        Fund::factory()->create(['start_year' => 2021, 'fund_manager_id' => $fundManager->id]);

        $response = $this->getJson('/api/funds?start_year=2020');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    /** @test */
    public function it_can_filter_funds_by_company()
    {
        $fundManager = FundManager::factory()->create();
        $company = Company::factory()->create();
        $fund1 = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);
        $fund2 = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);

        $fund1->companies()->attach($company->id);

        $response = $this->getJson('/api/funds?company_id=' . $company->id);

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    /** @test */
    public function it_excludes_soft_deleted_funds_from_list()
    {
        $fundManager = FundManager::factory()->create();
        Fund::factory()->create(['fund_manager_id' => $fundManager->id]);
        Fund::factory()->create(['fund_manager_id' => $fundManager->id, 'deleted_at' => now()]);

        $response = $this->getJson('/api/funds');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    /** @test */
    public function it_can_show_a_single_fund()
    {
        $fundManager = FundManager::factory()->create();
        $fund = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);

        $response = $this->getJson('/api/funds/' . $fund->id);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => $fund->name]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_fund()
    {
        $response = $this->getJson('/api/funds/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_update_a_fund()
    {
        $fundManager = FundManager::factory()->create();
        $fund = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);

        $response = $this->putJson('/api/funds/' . $fund->id, [
            'name' => 'Updated Fund Name',
            'start_year' => 2022,
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Updated Fund Name']);

        $this->assertDatabaseHas('funds', [
            'id' => $fund->id,
            'name' => 'Updated Fund Name',
            'start_year' => 2022,
        ]);
    }

    /** @test */
    public function it_can_update_fund_aliases()
    {
        $fundManager = FundManager::factory()->create();
        $fund = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);
        Alias::factory()->create(['fund_id' => $fund->id, 'name' => 'Old Alias']);

        $response = $this->putJson('/api/funds/' . $fund->id, [
            'aliases' => ['New Alias 1', 'New Alias 2'],
        ]);

        $response->assertStatus(200);

        $fund->refresh();
        $this->assertCount(2, $fund->aliases);
        $this->assertDatabaseMissing('aliases', ['name' => 'Old Alias']);
        $this->assertDatabaseHas('aliases', ['name' => 'New Alias 1']);
    }

    /** @test */
    public function it_can_update_fund_company_associations()
    {
        $fundManager = FundManager::factory()->create();
        $fund = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);
        $company1 = Company::factory()->create();
        $company2 = Company::factory()->create();
        $company3 = Company::factory()->create();

        $fund->companies()->attach($company1->id);

        $response = $this->putJson('/api/funds/' . $fund->id, [
            'company_ids' => [$company2->id, $company3->id],
        ]);

        $response->assertStatus(200);

        $fund->refresh();
        $this->assertCount(2, $fund->companies);
        $this->assertTrue($fund->companies->contains($company2));
        $this->assertFalse($fund->companies->contains($company1));
    }

    /** @test */
    public function it_can_soft_delete_a_fund()
    {
        $fundManager = FundManager::factory()->create();
        $fund = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);

        $response = $this->deleteJson('/api/funds/' . $fund->id);

        $response->assertStatus(204);

        $this->assertSoftDeleted('funds', ['id' => $fund->id]);
    }

    /** @test */
    public function it_preserves_aliases_and_companies_on_soft_delete()
    {
        $fundManager = FundManager::factory()->create();
        $fund = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);
        $alias = Alias::factory()->create(['fund_id' => $fund->id]);
        $company = Company::factory()->create();
        $fund->companies()->attach($company->id);

        $this->deleteJson('/api/funds/' . $fund->id);

        $this->assertDatabaseHas('aliases', ['id' => $alias->id]);
        $this->assertDatabaseHas('company_fund', ['fund_id' => $fund->id, 'company_id' => $company->id]);
    }
}
