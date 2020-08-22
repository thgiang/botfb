<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhiteListId extends Model
{
    protected $fillable = ['bot_id', 'fb_id', 'id'];
}
