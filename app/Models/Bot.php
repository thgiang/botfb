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
    protected $fillable = ['cookie', 'name', 'frequency', 'proxy', 'sticker_collection_id', 'comment_on', 'comment_content',
        'time_between', 'start_time', 'end_time', 'reaction_type', 'bot_target', 'black_list'];

}
