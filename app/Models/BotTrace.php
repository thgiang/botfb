<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property  string code
 * @property  string bot_facebook_uid
 * @property  int bot_id
 * @property  boolean success
 * @property  string content
 * @property string data
 */
class BotTrace extends Model
{
    public static function SaveTrace($code, $success, $bot_id, $bot_facebook_id, $content, $data = array()) {
        if (!env('BOT_DEBUG', false)) {
            return;
        }

        $trace = new BotTrace();
        $trace->code = $code;
        $trace->success = $success;
        $trace->bot_id = $bot_id;
        $trace->bot_facebook_uid = $bot_facebook_id;
        $trace->content = $content;
        $trace->data = json_encode($data);
        $trace->save();
    }
}
