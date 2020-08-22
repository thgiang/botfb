<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemProxy extends Model
{
    protected $fillable = ['proxy', 'is_live', 'expired', 'bot_id'];
}
