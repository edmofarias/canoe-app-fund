<?php

namespace App\Services;

use App\Models\DuplicateWarning;
use Illuminate\Database\Eloquent\Collection;

class DuplicateWarningService
{
    /**
     * List all unresolved duplicate warnings with fund details.
     */
    public function listUnresolvedWarnings(): Collection
    {
        return DuplicateWarning::unresolved()
            ->with([
            'fund1.fundManager',
            'fund1.aliases',
            'fund1.companies',
            'fund2.fundManager',
            'fund2.aliases',
            'fund2.companies'
        ])
            ->get();
    }
}
