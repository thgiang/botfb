<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string cookie
 * @property int frequency
 * @property string proxy
 */
class Bot extends Model
{
    protected $fillable = ['cookie', 'name', 'proxy', 'reaction_type', 'bot_target', 'comment_on', 'comment_sticker_collection',
        'comment_content', 'comment_image_url', 'black_list', 'frequency', 'start_time', 'end_time'];
}
