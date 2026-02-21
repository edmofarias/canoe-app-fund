<?php

namespace App\Services;

use App\Models\FundManager;
use Illuminate\Database\Eloquent\Collection;

class FundManagerService
{
    /**
     * Create a new fund manager.
     */
    public function createFundManager(array $data): FundManager
    {
        return FundManager::create([
            'name' => $data['name'],
        ]);
    }

    /**
     * List all fund managers (excluding soft-deleted).
     */
    public function listFundManagers(): Collection
    {
        return FundManager::all();
    }

    /**
     * Soft delete a fund manager.
     */
    public function deleteFundManager(FundManager $fundManager): bool
    {
        return $fundManager->delete();
    }
}
