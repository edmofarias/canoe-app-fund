<?php

namespace App\Listeners;

use App\Events\DuplicateFundWarning;
use App\Models\DuplicateWarning;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class DuplicateWarningListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param DuplicateFundWarning $event
     * @return void
     */
    public function handle(DuplicateFundWarning $event): void
    {
        DuplicateWarning::create([
            'fund_id_1' => $event->fundId1,
            'fund_id_2' => $event->fundId2,
            'resolved' => false,
        ]);
    }
}
