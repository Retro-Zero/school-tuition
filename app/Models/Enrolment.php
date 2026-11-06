<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrolment extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'subject_id', 'term', 'quantity'];

    public const SUMMER = 'summer';

    public const EXTRA = 'extra';

    protected function casts(): array
    {
        return ['quantity' => 'integer'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
