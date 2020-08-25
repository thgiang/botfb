<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property string cookie
 * @property int reaction_frequency
 * @property int comment_frequency
 * @property string proxy
 * @property boolean is_active
 * @property boolean white_list_reaction_on
 * @property boolean white_list_comment_on
 * @property boolean white_group_comment_on
 * @property boolean white_group_reaction_on
 * @property boolean comment_on
 * @property boolean reaction_on
 * @property boolean next_reaction_time
 * @property boolean next_comment_time
 * @property string error_log
 * @property int count_error
 * @property string facebook_uid
 * @property mixed id
 * @property mixed trace_code
 * @property mixed reaction_type
 * @property mixed run_time
 * @property mixed write_post_owner_name_to_image
 * @property mixed comment_content
 * @property mixed black_list
 * @property null white_group
 * @property null white_list
 * @property mixed bot_target
 */
class Bot extends Model
{
    protected $fillable = ['cookie', 'name', 'is_active', 'proxy', 'bot_target', 'reaction_type', 'reaction_on', 'comment_on', 'comment_sticker_collection',
        'comment_content', 'comment_image_url', 'black_list', 'white_list', 'white_group', 'white_list_comment_on', 'white_group_reaction_on', 'white_group_comment_on', 'white_group_reaction_on', 'reaction_frequency', 'comment_frequency', 'start_time', 'end_time', 'run_time', 'facebook_uid', 'white_list_run_mode', 'white_group_run_mode', 'write_post_owner_name_to_image', 'error_log'];
}
