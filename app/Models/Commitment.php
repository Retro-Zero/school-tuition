<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commitment extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'financial_year_id', 'due_on', 'amount', 'label'];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'due_on' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }
}
