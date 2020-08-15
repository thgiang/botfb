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
            sendMessageTelegram("WARNING: Hàm lấy proxy Tinsoft ko trả về đc proxy nên bot phải chờ tới lần chạy sau");
            Log::error('WARNING: Hàm lấy proxy Tinsoft ko trả về đc proxy nên bot phải chờ tới lần chạy sau');
            return;
        }

        // Lấy thông tin token
        $tokenInfo = FacebookGet('me', array(), $token->token, $proxy);
        if (empty($tokenInfo) || empty($tokenInfo->id)) {
            $token->is_live = false;
            $token->save();

            $countLiveToken =  SystemToken::where('is_live', true)->count();
            sendMessageTelegram("WARNING: Thêm 1 token vừa die, hệ thống chỉ còn ".$countLiveToken." token");
            Log::error("WARNING: Thêm 1 token vừa die, hệ thống chỉ còn ".$countLiveToken." token");
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
                $bot = Bot::where('id', $botId)->first();
                if (!$bot) {
                    continue;
                }
				// Nếu bot này ở chế độ chạy cùng với tương tác dạo khác, thì xét next_comment_time, next_reaction_time xem đã tới giờ chưa tránh chạy liên tục
				if ($bot->white_list_run_mode == 'mixed') {
					if ($bot->next_comment_time > time() && $bot->next_reaction_time > time()) {
						continue;
					}
				}
                $postId = str_replace($this->fb_id . '_', '', $post->id);
                // Tìm trong history xem đã tương tác với bài này chưa
                $history = BotLog::where('bot_fid', $bot->facebook_uid)->where('post_id', $postId)->first();
                if (!$history) {
                    BotFacebook::dispatch($botId, $postId, 'white_list');
                }
            }
        }
    }
}
