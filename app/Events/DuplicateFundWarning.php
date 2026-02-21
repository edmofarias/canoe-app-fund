<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DuplicateFundWarning implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $fundId1;
    public int $fundId2;
    public string $detectedAt;

    /**
     * Create a new event instance.
     *
     * @param int $fundId1 First fund ID
     * @param int $fundId2 Second fund ID
     */
    public function __construct(int $fundId1, int $fundId2)
    {
        $this->fundId1 = $fundId1;
        $this->fundId2 = $fundId2;
        $this->detectedAt = now()->toIso8601String();
    }
}
