<?php

namespace App\Actions\Pricing;

use App\Models\FinancialYear;
use App\Models\PriceBook;

/**
 * Only this action may create a price book. Read paths (reports, lists) must never
 * write — Tuition::forYearId() used to firstOrCreate() and could INSERT on a GET.
 */
class EnsurePriceBook
{
    public function handle(FinancialYear $year): PriceBook
    {
        return PriceBook::with(['baseTuitions', 'unitPrices'])
            ->firstOrCreate(['financial_year_id' => $year->id]);
    }
}
