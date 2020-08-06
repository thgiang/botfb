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

        // Chọn $postId theo quy tắc đã setup
        $postIds = getPostsFromNewFeed($bot->cookie, $bot->proxy);
        if (!is_array($postIds)|| empty($postIds)) {
            $bot->error_log = 'Không tìm được bài viết phù hợp vì vậy sẽ thử lại ở phiên chạy sau';
            $bot->save();
            return;
        } else {
            $postId = $postIds[rand(0, count($postIds) - 1)];
        }

        // Chọn theo cảm xúc khách setup, nếu khách setup số linh tinh thì chọn random 1 trong các cảm xúc
        $reactions = array(1, 2, 3, 4, 6, 8, 16);
        if (in_array($bot->reaction_type, $reactions)) {
            $reactionType = $bot->reaction_type;
        } else {
            $reactionType = $reactions[rand(0, count($reactions) - 1)];
        }

        // Gửi reaction
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

        // Nếu bật Auto comment
        if ($bot->comment_on) {
            // Build nội dung comment
            $commentContent = RandomComment();
            $comments = explode(PHP_EOL, $bot->comment_content);
            if (count($comments) > 0) {
                $commentContent = DoShortCode($comments[rand(0, count($comments) - 1)]);
            }

            // Random Sticker ID nếu người dùng có chọn collection
            $stickerId = null;
            if (!empty($bot->sticker_collection_id)) {
                $tmpStickerId = randomStickerOfCollection($bot->cookie, $fbDtg, $bot->sticker_collection_id);
                if ($tmpStickerId !== false) {
                    $stickerId = $tmpStickerId;
                }
            }

            // Gửi comment
            $comment = commentPostByCookie($bot->cookie, $fbDtg, $postId, $commentContent, $stickerId, $bot->proxy);
            if ($comment) {
                $botLog = new BotLog();
                $botLog->bot_id = $bot->id;
                $botLog->action = 'COMMENT';
                $botLog->comment_id = $comment;
                $botLog->comment_content = $commentContent;
                $botLog->post_id = $postId;
                $botLog->save();

                // Update thời gian chạy lần tiếp theo. Có thể sai lệch 25% so với thời gian setup để trông tự nhiên hơn
                if ($bot->start_time > $bot->end_time) {
                    // Trường hợp chạy đêm, ví dụ 23h hôm trc tới 8h sáng hôm sau
                    $start_time = strtotime('yesterday +'.$bot->start_time.'hours');
                    $end_time = strtotime('today +'.$bot->end_time.'hours');
                } else {
                    $start_time = strtotime('today +'.$bot->start_time.'hours');
                    $end_time = strtotime('today +'.$bot->end_time.'hours');
                }
                $bot->next_run_time = min(max($start_time, time() + $bot->frequency * rand(75, 125) / 100 * 60), $end_time);
                if ($bot->next_run_time >= $end_time) {
                    // Nếu quá giờ chạy rồi thì thôi để mai
                    $bot->next_run_time = $start_time + 24*60*60;
                }
                $bot->save();
            }
        }
    }
}
