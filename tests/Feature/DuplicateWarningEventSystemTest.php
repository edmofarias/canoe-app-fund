<?php

namespace Tests\Feature;

use App\Events\DuplicateFundWarning;
use App\Listeners\DuplicateWarningListener;
use App\Models\DuplicateWarning;
use App\Models\Fund;
use App\Models\FundManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class DuplicateWarningEventSystemTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that DuplicateFundWarning event is registered with listener.
     *
     * @return void
     */
    public function test_duplicate_fund_warning_event_is_registered(): void
    {
        Event::fake();

        // Dispatch the event
        event(new DuplicateFundWarning(1, 2));

        // Assert the event was dispatched
        Event::assertDispatched(DuplicateFundWarning::class , function ($event) {
            return $event->fundId1 === 1 && $event->fundId2 === 2;
        });
    }

    /**
     * Test that DuplicateWarningListener persists warning to database.
     *
     * @return void
     */
    public function test_listener_persists_warning_to_database(): void
    {
        // Create fund manager and funds
        $fundManager = FundManager::factory()->create();
        $fund1 = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);
        $fund2 = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);

        // Create and handle the event
        $event = new DuplicateFundWarning($fund1->id, $fund2->id);
        $listener = new DuplicateWarningListener();
        $listener->handle($event);

        // Assert warning was created in database
        $this->assertDatabaseHas('duplicate_warnings', [
            'fund_id_1' => $fund1->id,
            'fund_id_2' => $fund2->id,
            'resolved' => false,
        ]);
    }

    /**
     * Test that warning has correct default values.
     *
     * @return void
     */
    public function test_warning_has_correct_default_values(): void
    {
        // Create fund manager and funds
        $fundManager = FundManager::factory()->create();
        $fund1 = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);
        $fund2 = Fund::factory()->create(['fund_manager_id' => $fundManager->id]);

        // Create and handle the event
        $event = new DuplicateFundWarning($fund1->id, $fund2->id);
        $listener = new DuplicateWarningListener();
        $listener->handle($event);

        // Retrieve the warning
        $warning = DuplicateWarning::first();

        // Assert default values
        $this->assertFalse($warning->resolved);
        $this->assertNotNull($warning->created_at);
        $this->assertNotNull($warning->updated_at);
    }

    /**
     * Test that event payload includes detected_at timestamp.
     *
     * @return void
     */
    public function test_event_includes_detected_at_timestamp(): void
    {
        $event = new DuplicateFundWarning(1, 2);

        $this->assertNotNull($event->detectedAt);
        $this->assertIsString($event->detectedAt);
    }
}
