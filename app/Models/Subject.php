<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'key', 'sort_order'];

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class);
    }
}
