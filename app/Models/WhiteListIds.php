<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhiteListIds extends Model
{
    protected $fillable = ['bot_id', 'fb_id', 'id'];
}
