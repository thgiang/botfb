<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemToken extends Model
{
    protected $fillable = ['token', 'is_live'];
}
