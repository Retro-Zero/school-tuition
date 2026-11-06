<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyField extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sort_order'];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
