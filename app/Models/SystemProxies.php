<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemProxies extends Model
{
    protected $fillable = ['proxy', 'is_live', 'expired', 'bot_id'];
}
