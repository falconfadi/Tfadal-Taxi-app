<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreezeReason extends Model
{
    use HasFactory;
    protected $fillable = [
        'driver_id',
        'reason',
        'is_freeze'
    ];
}
