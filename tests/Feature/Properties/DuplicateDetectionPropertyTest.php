<?php

namespace Tests\Feature\Properties;

use App\Events\DuplicateFundWarning;
use App\Models\Alias;
use App\Models\Fund;
use App\Models\FundManager;
use App\Services\DuplicateDetectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DuplicateDetectionPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @group property-based
     * @group canoe-app-funds
     */
    public function it_satisfies_property_7_duplicate_detection_event_emission()
    {
        // Feature: canoe-app-funds, Property 7: Duplicate Detection Event Emission
        // Validates: Requirements 4.5, 6.5, 10.1, 10.2, 10.3, 10.4, 10.5

        $service = new DuplicateDetectionService();

        for ($i = 0; $i < 100; $i++) {
            // Reset database for each iteration
            $this->refreshDatabase();
            Event::fake();

            $scenario = $this->generateDuplicateScenario();

            // Execute duplicate detection
            $service->checkForDuplicates($scenario['targetFund']);

            // Assert event emission based on scenario
            if ($scenario['shouldDetectDuplicate']) {
                Event::assertDispatched(DuplicateFundWarning::class , function ($event) use ($scenario) {
                    return ($event->fundId1 === $scenario['targetFund']->id &&
                    $event->fundId2 === $scenario['existingFund']->id) ||
                    ($event->fundId1 === $scenario['existingFund']->id &&
                    $event->fundId2 === $scenario['targetFund']->id);
                });
            }
            else {
                Event::assertNotDispatched(DuplicateFundWarning::class);
            }
        }
    }

    /**
     * Generate a randomized duplicate detection scenario.
     *
     * @return array
     */
    private function generateDuplicateScenario(): array
    {
        $scenarioType = fake()->randomElement([
            'fund_name_matches_fund_name',
            'fund_name_matches_alias',
            'alias_matches_fund_name',
            'alias_matches_alias',
            'case_insensitive_match',
            'different_fund_manager_no_match',
            'no_match',
            'soft_deleted_no_match',
        ]);

        $fundManager = FundManager::factory()->create();

        switch ($scenarioType) {
            case 'fund_name_matches_fund_name':
                // Scenario: Fund name matches existing fund name (same manager)
                $matchingName = fake()->company() . ' Fund ' . uniqid();
                $existingFund = Fund::factory()->create([
                    'name' => $matchingName,
                    'fund_manager_id' => $fundManager->id,
                ]);
                $targetFund = Fund::factory()->create([
                    'name' => $matchingName,
                    'fund_manager_id' => $fundManager->id,
                ]);
                return [
                    'targetFund' => $targetFund,
                    'existingFund' => $existingFund,
                    'shouldDetectDuplicate' => true,
                ];

            case 'fund_name_matches_alias':
                // Scenario: Fund name matches existing fund's alias (same manager)
                $matchingName = fake()->company() . ' Fund ' . uniqid();
                $existingFund = Fund::factory()->create([
                    'fund_manager_id' => $fundManager->id,
                ]);
                Alias::factory()->create([
                    'name' => $matchingName,
                    'fund_id' => $existingFund->id,
                ]);
                $targetFund = Fund::factory()->create([
                    'name' => $matchingName,
                    'fund_manager_id' => $fundManager->id,
                ]);
                return [
                    'targetFund' => $targetFund->fresh('aliases'),
                    'existingFund' => $existingFund->fresh('aliases'),
                    'shouldDetectDuplicate' => true,
                ];

            case 'alias_matches_fund_name':
                // Scenario: Fund alias matches existing fund name (same manager)
                $matchingName = fake()->company() . ' Fund ' . uniqid();
                $existingFund = Fund::factory()->create([
                    'name' => $matchingName,
                    'fund_manager_id' => $fundManager->id,
                ]);
                $targetFund = Fund::factory()->create([
                    'fund_manager_id' => $fundManager->id,
                ]);
                Alias::factory()->create([
                    'name' => $matchingName,
                    'fund_id' => $targetFund->id,
                ]);
                return [
                    'targetFund' => $targetFund->fresh('aliases'),
                    'existingFund' => $existingFund->fresh('aliases'),
                    'shouldDetectDuplicate' => true,
                ];

            case 'alias_matches_alias':
                // Scenario: Fund alias matches existing fund's alias (same manager)
                // Note: Since aliases have unique constraint, we test fund name matching alias instead
                $matchingName = fake()->company() . ' Fund ' . uniqid();
                $existingFund = Fund::factory()->create([
                    'fund_manager_id' => $fundManager->id,
                ]);
                Alias::factory()->create([
                    'name' => $matchingName,
                    'fund_id' => $existingFund->id,
                ]);
                $targetFund = Fund::factory()->create([
                    'name' => $matchingName, // Fund name matches existing alias
                    'fund_manager_id' => $fundManager->id,
                ]);
                return [
                    'targetFund' => $targetFund->fresh('aliases'),
                    'existingFund' => $existingFund->fresh('aliases'),
                    'shouldDetectDuplicate' => true,
                ];

            case 'case_insensitive_match':
                // Scenario: Case-insensitive matching
                $baseName = fake()->company() . ' Fund ' . uniqid();
                $existingFund = Fund::factory()->create([
                    'name' => strtolower($baseName),
                    'fund_manager_id' => $fundManager->id,
                ]);
                $targetFund = Fund::factory()->create([
                    'name' => strtoupper($baseName),
                    'fund_manager_id' => $fundManager->id,
                ]);
                return [
                    'targetFund' => $targetFund,
                    'existingFund' => $existingFund,
                    'shouldDetectDuplicate' => true,
                ];

            case 'different_fund_manager_no_match':
                // Scenario: Same name but different fund manager (no duplicate)
                $matchingName = fake()->company() . ' Fund ' . uniqid();
                $otherFundManager = FundManager::factory()->create();
                $existingFund = Fund::factory()->create([
                    'name' => $matchingName,
                    'fund_manager_id' => $otherFundManager->id,
                ]);
                $targetFund = Fund::factory()->create([
                    'name' => $matchingName,
                    'fund_manager_id' => $fundManager->id,
                ]);
                return [
                    'targetFund' => $targetFund,
                    'existingFund' => $existingFund,
                    'shouldDetectDuplicate' => false,
                ];

            case 'soft_deleted_no_match':
                // Scenario: Existing fund is soft-deleted (no duplicate)
                $matchingName = fake()->company() . ' Fund ' . uniqid();
                $existingFund = Fund::factory()->create([
                    'name' => $matchingName,
                    'fund_manager_id' => $fundManager->id,
                    'deleted_at' => now(),
                ]);
                $targetFund = Fund::factory()->create([
                    'name' => $matchingName,
                    'fund_manager_id' => $fundManager->id,
                ]);
                return [
                    'targetFund' => $targetFund,
                    'existingFund' => $existingFund,
                    'shouldDetectDuplicate' => false,
                ];

            case 'no_match':
            default:
                // Scenario: No matching names or aliases (no duplicate)
                $existingFund = Fund::factory()->create([
                    'name' => fake()->company() . ' Fund A ' . uniqid(),
                    'fund_manager_id' => $fundManager->id,
                ]);
                $targetFund = Fund::factory()->create([
                    'name' => fake()->company() . ' Fund B ' . uniqid(),
                    'fund_manager_id' => $fundManager->id,
                ]);
                return [
                    'targetFund' => $targetFund,
                    'existingFund' => $existingFund,
                    'shouldDetectDuplicate' => false,
                ];
        }
    }
}
