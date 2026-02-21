<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Database\Eloquent\Collection;

class CompanyService
{
    /**
     * Create a new company.
     */
    public function createCompany(array $data): Company
    {
        return Company::create([
            'name' => $data['name'],
        ]);
    }

    /**
     * List all companies (excluding soft-deleted).
     */
    public function listCompanies(): Collection
    {
        return Company::all();
    }

    /**
     * Soft delete a company.
     */
    public function deleteCompany(Company $company): bool
    {
        return $company->delete();
    }
}
