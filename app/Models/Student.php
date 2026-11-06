<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'financial_year_id', 'national_id', 'first_name', 'last_name', 'phone',
        'grade_level_id', 'study_field_id', 'is_active', 'coupon_id',
        'council_amount', 'olympiad_amount', 'carry_over_debt',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'council_amount' => 'integer',
            'olympiad_amount' => 'integer',
            'carry_over_debt' => 'integer',
        ];
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function studyField(): BelongsTo
    {
        return $this->belongsTo(StudyField::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function commitments(): HasMany
    {
        return $this->hasMany(Commitment::class);
    }

    public function scopeForYear(Builder $query, int $yearId): Builder
    {
        return $query->where('financial_year_id', $yearId);
    }

    public function scopeFiltered(Builder $query, ?string $gradeLevel, ?string $studyField): Builder
    {
        return $query
            ->when($gradeLevel && $gradeLevel !== 'همه',
                fn (Builder $q) => $q->whereHas('gradeLevel', fn ($g) => $g->where('name', $gradeLevel)))
            ->when($studyField && $studyField !== 'همه',
                fn (Builder $q) => $q->whereHas('studyField', fn ($f) => $f->where('name', $studyField)));
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
