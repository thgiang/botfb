<?php

namespace App\Jobs;

use App\Models\Bot;
use App\Models\BotLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BotFacebook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $botId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($botId)
    {
        $this->botId = $botId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bot = Bot::where('id', $this->botId)->first();
        if (!$bot) {
            return;
        }
        /*
        if (empty($bot->proxy)) {
            Log::error("Bot ID ".$bot->id." không có Proxy nên ko dám chạy kẻo bay Acc :D");
            return;
        }
        */

        if(empty($bot->proxy)) {
            $bot->proxy = null;
        }

        $fbDtg = getFbDtsg($bot->cookie, $bot->proxy);
        if (!$fbDtg) {
            $bot->is_valid = false;
            $bot->error_log = 'Đăng nhập không thành công do Cookie hoặc Proxy die. Tài khoản bị dừng chạy lúc '.date("d/m/Y H:i:s").'';
            $bot->save();

            Log::error("Bot ID ".$bot->id." Đăng nhập không thành công!");
            return;
        }


        $reactionType = rand(1, 4);
        $postId = '2613233488893674';
        $reaction = reactionPostByCookie($bot->cookie, $fbDtg, $postId, $reactionType, $bot->proxy);
        if ($reaction) {
            $botLog = new BotLog();
            $botLog->bot_id = $bot->id;
            $botLog->action = $reactionType;
            $botLog->post_id = $postId;
            $botLog->save();

            // Update next run time of bot
            $bot->next_run_time = time() + $bot->frequency * 60;
            $bot->save();
        }

        $commentContent = RandomComment();
        $postId = '2613233488893674';
        $comment = commentPostByCookie($bot->cookie, $fbDtg, $postId, $commentContent, null, $bot->proxy);
        if ($comment) {
            $botLog = new BotLog();
            $botLog->bot_id = $bot->id;
            $botLog->action = 'COMMENT';
            $botLog->comment_id = $comment;
            $botLog->post_id = $postId;
            $botLog->save();

            // Update next run time of bot
            $bot->next_run_time = time() + $bot->frequency * 60;
            $bot->save();
        }
    }
}
