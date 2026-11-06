<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BaseTuition extends Model
{
    use HasFactory;

    protected $fillable = ['price_book_id', 'grade_level_id', 'amount'];

    protected function casts(): array
    {
        return ['amount' => 'integer'];
    }

    public function priceBook(): BelongsTo
    {
        return $this->belongsTo(PriceBook::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }
}
