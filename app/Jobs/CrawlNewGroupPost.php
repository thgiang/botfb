<?php

namespace App\Jobs;

use App\Models\Bot;
use App\Models\BotLog;
use App\Models\SystemToken;
use App\Models\WhiteGroupId;
use App\Models\WhiteListId;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CrawlNewGroupPost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $timeout = 50;

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
        $whiteIds = WhiteGroupId::where('fb_id', $this->fb_id)->get();

        $foundBot = null;
        $logs = "";
        foreach ($whiteIds AS $white) {
            $bot = Bot::where('id', $white->bot_id)->first();
			// Nếu bot này ở chế độ chạy cùng với tương tác dạo khác, thì xét next_comment_time, next_reaction_time xem đã tới giờ chưa tránh chạy liên tục
			if (isset($bot->white_group_run_mode) && $bot->white_group_run_mode == 'mixed') {
				if ($bot->next_comment_time > time() && $bot->next_reaction_time > time()) {
					continue;
				}
			}

            if ($bot) {
                if (checkCookieJoinedGroup($bot->cookie, $this->fb_id, $bot->proxy)) {
                    $foundBot = $bot;
                    break;
                } else {
                    $logs .= "Bot ID " . $white->bot_id . " tồn tại trong bảng Bot, có proxy " . $bot->proxy . " nhưng không quét được bài đăng của group " . $this->fb_id . " \n\n";
                    $logs .= "\n\nCode debug: \n";
                    $logs .= "checkCookieJoinedGroup($bot->cookie, $this->fb_id, $bot->proxy)";
                }
            } else {
                $logs .= "Bot ID " . $white->bot_id . " không tồn tại trong bảng bot \n\n";
            }
        }

        if ($foundBot == null) {
            sendMessageTelegram("WARNING: Đang quét bài mới của group " . $this->fb_id . " nhưng ko có bot nào quét đc bài viết của group này. \n\nLogs: " . $logs);
            Log::error("WARNING: Đang quét bài mới của group " . $this->fb_id . " nhưng ko có bot nào quét đc bài viết của group này");
            return;
        }

        // Gọi tất cả các bot,
        $posts = getPostsFromGroup($foundBot->cookie, $this->fb_id, $foundBot->proxy);
        foreach ($whiteIds as $white) {
            $bot = Bot::where('id', $white->bot_id)->first();
            if (!$bot) {
                continue;
            }

			// Nếu bot này ở chế độ chạy cùng với tương tác dạo khác, thì xét next_comment_time, next_reaction_time xem đã tới giờ chưa tránh chạy liên tục
			if ($bot->white_group_run_mode == 'mixed') {
				if ($bot->next_comment_time > time() && $bot->next_reaction_time > time()) {
					continue;
				}
			}

            $countPost = 0;
            foreach ($posts as $post) {
                // Nếu tương tác đủ 2 bài rồi thì break; mỗi bot chỉ cần thế thôi
                if ($countPost > config('bot . white_list_feed_limit')) {
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
