<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceBook extends Model
{
    use HasFactory;

    protected $fillable = ['financial_year_id', 'insurance_amount'];

    protected function casts(): array
    {
        return ['insurance_amount' => 'integer'];
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function baseTuitions(): HasMany
    {
        return $this->hasMany(BaseTuition::class);
    }

    public function unitPrices(): HasMany
    {
        return $this->hasMany(UnitPrice::class);
    }

    public function baseFor(int $gradeLevelId): int
    {
        return (int) ($this->baseTuitions->firstWhere('grade_level_id', $gradeLevelId)?->amount ?? 0);
    }

    public function unitFor(int $gradeLevelId, string $term): int
    {
        return (int) ($this->unitPrices
            ->first(fn ($p) => $p->grade_level_id === $gradeLevelId && $p->term === $term)?->amount ?? 0);
    }
}
