<?php

namespace App\Jobs;

use App\Models\Bot;
use App\Models\BotLog;
use App\Models\SystemToken;
use App\Models\WhiteGroupIds;
use App\Models\WhiteListIds;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CrawlNewGroupPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $fb_id;

    /**
     * Create a new job instance.
     *
     * @param string $fb_id
     */
    public function __construct($fb_id)
    {
        $this->fb_id = $fb_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Lấy 1 con bot đang coi group này là white_group với điều kiện nó phải join rồi để quét đc bài mới
        $bots = WhiteGroupIds::where('fb_id', $this->fb_id)->get();

        $bot = null;
        foreach ($bots AS $b) {
			$bt = Bot::where('id', $b->id)->first();
            //if ($bt && checkCookieJoinedGroup($bt->cookie, $this->fb_id, $bt->proxy)) {
				$bot = $bt;
                break;
            //}
        }
        if ($bot == null) {
            sendMessageTelegram("WARNING: Đang quét bài mới của group " . $this->fb_id . " nhưng ko có bot nào quét đc bài viết của group này");
            Log::error("WARNING: Đang quét bài mới của group " . $this->fb_id . " nhưng ko có bot nào quét đc bài viết của group này");
            return;
        }

        // Gọi tất cả các bot,
        $posts = getPostsFromGroup($bot->cookie, $this->fb_id, $bot->proxy);
        foreach ($bots as $bot) {
            $countPost = 0;
            foreach ($posts as $post) {
                // Nếu tương tác đủ 2 bài rồi thì break; mỗi bot chỉ cần thế thôi
                if ($countPost > config('bot.white_list_feed_limit')) {
                    break;
                }
                // Tìm xem bot này đã tương tác với bài viết này chưa
                $botLog = BotLog::where('bot_fid', $bot->facebook_uid)->where('post_id', $post->post_id)->first();
                if (!$botLog) {
                    BotFacebook::dispatch($bot->id, $post->post_id, 'white_group', (array)$post);
                    $countPost++;
                }
            }
        }
    }
}
