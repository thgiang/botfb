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
    protected $fillable = ['cookie', 'name', 'proxy', 'bot_target', 'reaction_type', 'like_on', 'comment_on', 'comment_sticker_collection',
        'comment_content', 'comment_image_url', 'black_list', 'white_list', 'like_frequency', 'comment_frequency', 'start_time', 'end_time'];
}
