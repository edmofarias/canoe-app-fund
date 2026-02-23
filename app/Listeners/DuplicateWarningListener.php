<?php

namespace App\Listeners;

use App\Events\DuplicateFundWarning;
use App\Models\DuplicateWarning;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Log;

class DuplicateWarningListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'duplicate_fund_warning';

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 5;

    /**
     * Handle the event.
     *
     * @param DuplicateFundWarning $event
     * @return void
     */
    public function handle(DuplicateFundWarning $event): void
    {
        // Check if this warning already exists
        $exists = DuplicateWarning::where(function ($query) use ($event) {
            $query->where('fund_id_1', $event->fundId1)
                  ->where('fund_id_2', $event->fundId2);
        })->orWhere(function ($query) use ($event) {
            $query->where('fund_id_1', $event->fundId2)
                  ->where('fund_id_2', $event->fundId1);
        })->exists();

        if ($exists) {
            Log::info('Duplicate warning already exists, skipping', [
                'fund_id_1' => $event->fundId1,
                'fund_id_2' => $event->fundId2,
            ]);
            return;
        }

        DuplicateWarning::create([
            'fund_id_1' => $event->fundId1,
            'fund_id_2' => $event->fundId2,
            'resolved' => false,
        ]);

        Log::info('duplicate fund warning has saved in the DB', [
            'fund_id_1' => $event->fundId1,
            'fund_id_2' => $event->fundId2,
            'detected_at' => $event->detectedAt
        ]);
    }
}
