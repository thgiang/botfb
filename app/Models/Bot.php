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
    protected $fillable = ['cookie', 'name', 'proxy', 'bot_target', 'reaction_type', 'reaction_on', 'comment_on', 'comment_sticker_collection',
        'comment_content', 'comment_image_url', 'black_list', 'white_list', 'white_group', 'white_list_comment_on', 'white_group_reaction_on', 'white_group_comment_on', 'white_group_reaction_on', 'reaction_frequency', 'comment_frequency', 'start_time', 'end_time', 'run_time', 'facebook_uid', 'white_list_run_mode', 'white_group_run_mode'];
}
