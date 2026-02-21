<?php

namespace App\Services;

use App\Events\DuplicateFundWarning;
use App\Models\Fund;

class DuplicateDetectionService
{
    /**
     * Check for duplicate funds based on name and alias matching.
     * Emits DuplicateFundWarning event when duplicates are found.
     *
     * @param Fund $fund The fund to check for duplicates
     * @return void
     */
    public function checkForDuplicates(Fund $fund): void
    {
        $fundManagerId = $fund->fund_manager_id;

        // Get all funds from same manager (excluding current fund)
        $existingFunds = Fund::where('fund_manager_id', $fundManagerId)
            ->where('id', '!=', $fund->id)
            ->whereNull('deleted_at')
            ->with('aliases')
            ->get();

        // Collect all identifiers for the current fund (name + aliases)
        $fundNames = [$fund->name];
        $fundAliases = $fund->aliases->pluck('name')->toArray();
        $allFundIdentifiers = array_merge($fundNames, $fundAliases);

        // Check each existing fund for matches
        foreach ($existingFunds as $existingFund) {
            $existingNames = [$existingFund->name];
            $existingAliases = $existingFund->aliases->pluck('name')->toArray();
            $allExistingIdentifiers = array_merge($existingNames, $existingAliases);

            // Case-insensitive comparison
            foreach ($allFundIdentifiers as $identifier) {
                foreach ($allExistingIdentifiers as $existingIdentifier) {
                    if (strcasecmp($identifier, $existingIdentifier) === 0) {
                        $this->emitDuplicateWarning($fund->id, $existingFund->id);
                        break 2; // Exit both loops once duplicate found
                    }
                }
            }
        }
    }

    /**
     * Emit a duplicate fund warning event.
     *
     * @param int $fundId1 First fund ID
     * @param int $fundId2 Second fund ID
     * @return void
     */
    private function emitDuplicateWarning(int $fundId1, int $fundId2): void
    {
        event(new DuplicateFundWarning($fundId1, $fundId2));
    }
}
