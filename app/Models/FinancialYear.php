<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FinancialYear extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'starts_on', 'ends_on', 'is_open'];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'is_open' => 'boolean'];
    }

    public function priceBook(): HasOne
    {
        return $this->hasOne(PriceBook::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function commitments(): HasMany
    {
        return $this->hasMany(Commitment::class);
    }

    /** Start year of a Jalali name like "1402-1403" (used when creating months). */
    public function startYear(): int
    {
        return (int) explode('-', $this->name)[0];
    }
}
