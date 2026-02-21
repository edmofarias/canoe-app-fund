<?php

namespace App\Services;

use App\Models\Fund;
use App\Models\Alias;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FundService
{
    protected DuplicateDetectionService $duplicateDetectionService;

    public function __construct(DuplicateDetectionService $duplicateDetectionService)
    {
        $this->duplicateDetectionService = $duplicateDetectionService;
    }

    /**
     * Create a new fund with aliases and company associations.
     */
    public function createFund(array $data): Fund
    {
        $fund = DB::transaction(function () use ($data) {
            // Create the fund
            $fund = Fund::create([
                'name' => $data['name'],
                'start_year' => $data['start_year'],
                'fund_manager_id' => $data['fund_manager_id'],
            ]);

            // Create aliases if provided
            if (!empty($data['aliases'])) {
                foreach ($data['aliases'] as $aliasName) {
                    Alias::create([
                        'name' => $aliasName,
                        'fund_id' => $fund->id,
                    ]);
                }
            }

            // Sync company associations if provided
            if (!empty($data['company_ids'])) {
                $fund->companies()->sync($data['company_ids']);
            }

            // Reload relationships
            $fund->load(['fundManager', 'aliases', 'companies']);

            return $fund;
        });

        // Trigger duplicate detection
        $this->duplicateDetectionService->checkForDuplicates($fund);

        return $fund;
    }

    /**
     * List funds with optional filters.
     */
    public function listFunds(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = Fund::with(['fundManager', 'aliases', 'companies']);

        // Apply name filter (partial, case-insensitive)
        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        // Apply fund_manager_id filter
        if (!empty($filters['fund_manager_id'])) {
            $query->where('fund_manager_id', $filters['fund_manager_id']);
        }

        // Apply start_year filter
        if (!empty($filters['start_year'])) {
            $query->where('start_year', $filters['start_year']);
        }

        // Apply company_id filter
        if (!empty($filters['company_id'])) {
            $query->whereHas('companies', function ($q) use ($filters) {
                $q->where('companies.id', $filters['company_id']);
            });
        }

        return $query->get();
    }

    /**
     * Get a single fund by ID with relationships.
     */
    public function getFund(int $id): ?Fund
    {
        return Fund::with(['fundManager', 'aliases', 'companies'])->find($id);
    }

    /**
     * Update an existing fund.
     */
    public function updateFund(Fund $fund, array $data): Fund
    {
        $fund = DB::transaction(function () use ($fund, $data) {
            // Update fund fields
            if (isset($data['name'])) {
                $fund->name = $data['name'];
            }
            if (isset($data['start_year'])) {
                $fund->start_year = $data['start_year'];
            }
            if (isset($data['fund_manager_id'])) {
                $fund->fund_manager_id = $data['fund_manager_id'];
            }
            $fund->save();

            // Update aliases if provided
            if (array_key_exists('aliases', $data)) {
                // Remove existing aliases
                $fund->aliases()->delete();

                // Create new aliases
                if (!empty($data['aliases'])) {
                    foreach ($data['aliases'] as $aliasName) {
                        // Check uniqueness
                        $existingAlias = Alias::where('name', $aliasName)->first();
                        if ($existingAlias) {
                            throw ValidationException::withMessages([
                                'aliases' => ["The alias '{$aliasName}' is already taken."]
                            ]);
                        }

                        Alias::create([
                            'name' => $aliasName,
                            'fund_id' => $fund->id,
                        ]);
                    }
                }
            }

            // Sync company associations if provided
            if (array_key_exists('company_ids', $data)) {
                $fund->companies()->sync($data['company_ids'] ?? []);
            }

            // Reload relationships
            $fund->load(['fundManager', 'aliases', 'companies']);

            return $fund;
        });

        // Trigger duplicate detection
        $this->duplicateDetectionService->checkForDuplicates($fund);

        return $fund;
    }

    /**
     * Soft delete a fund.
     */
    public function deleteFund(Fund $fund): bool
    {
        return $fund->delete();
    }
}
