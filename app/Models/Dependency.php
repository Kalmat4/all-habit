<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dependency extends Model
{
     protected $fillable = ['name', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function impulses(): HasMany
    {
        return $this->hasMany(Impulse::class);
    }
}

class Impulse extends Model
{
    protected $fillable = ['dependency_id', 'resisted', 'trigger', 'comment'];
    protected $casts = ['resisted' => 'boolean'];

    public function dependency(): BelongsTo
    {
        return $this->belongsTo(Dependency::class);
    }

    // resist rate за период: считаем на стороне БД, не тащим строки в PHP
    public function scopeBetween($q, Carbon $from, Carbon $to)
    {
        return $q->whereBetween('created_at', [$from, $to]);
    }
}
