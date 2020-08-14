<?php

namespace App\Jobs;

use App\Models\Bot;
use App\Models\BotLog;
use App\Models\SystemToken;
use App\Models\WhiteListIds;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CrawlNewPost implements ShouldQueue
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
        $token = SystemToken::where('is_live', true)->inRandomOrder()->first();
        if (!$token) {
            // TODO: Bắn thông báo lên telegram
            sendMessageTelegram("LỖI TO: KHÔNG CÒN TOKEN ĐỂ CRAWL BÀI VIẾT");
            Log::error('LỖI TO: KHÔNG CÒN TOKEN ĐỂ CRAWL BÀI VIẾT');
            return;
        }

        // Lấy proxy để request
        $proxy = getTinsoftProxy();
        if ($proxy == false) {
            return;
        }

        // Lấy thông tin token
        $tokenInfo = FacebookGet('me', array(), $token->token, $proxy);
        if (empty($tokenInfo) || empty($tokenInfo->id)) {
            $token->is_live = false;
            $token->save();
            return;
        }

        // Token OK, lấy 2 bài gần nhất
        $feed = FacebookGet($this->fb_id . '/feed', array('limit' => config('bot.white_list_feed_limit')), $token->token, $proxy);
        if (empty($feed) || empty($feed->data)) {
            return;
        }

        // Thêm các bài viết vào queue bot FB
        foreach ($feed->data as $post) {
            // Lấy tất cả các bot đang coi FB này là white list
            $botIds = WhiteListIds::select('bot_id')->where('fb_id', $this->fb_id)->get()->pluck('bot_id');
            foreach ($botIds as $botId) {
                $postId = str_replace($this->fb_id . '_', '', $post->id);
                // Tìm trong history xem đã tương tác với bài này chưa
                $history = BotLog::where('bot_id', $botId)->where('post_id', $postId)->first();
                if (!$history) {
                    BotFacebook::dispatch($botId, $postId);
                }
            }
        }
    }
}
