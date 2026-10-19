<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habit extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'color',
        'target_per_week',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(HabitLog::class);
    }
}
