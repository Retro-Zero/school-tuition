<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GradeLevel extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sort_order'];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function baseTuitions(): HasMany
    {
        return $this->hasMany(BaseTuition::class);
    }

    public function unitPrices(): HasMany
    {
        return $this->hasMany(UnitPrice::class);
    }
}
