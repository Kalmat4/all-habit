<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Impulse extends Model
{
    protected $fillable = [
        'dependency_id',
        'resisted',
        'trigger',
        'comment',
    ];
}
