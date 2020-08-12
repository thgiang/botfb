<?php

namespace App\Jobs;

use App\Models\Bot;
use App\Models\BotLog;
use Carbon\Carbon;
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
    private $postId;

    /**
     * Create a new job instance. Nếu truyền $postId thì bot sẽ tương tác ngay với post đó, ko cần quan tâm thời gian hẹn giờ
     *
     * @param int $botId
     * @param string $postId
     */
    public function __construct($botId, $postId = '')
    {
        $this->botId = $botId;
        $this->postId = $postId;
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

        if (empty($bot->proxy) || count(explode(':', $bot->proxy)) !== 2) {
            $bot->proxy = null;
        } else {
            // Kiểm tra proxy hoạt động ok không
            $tryTestProxy = 0;
            do {
                if ($tryTestProxy >= 3) {
                    $bot->count_error = config('bot.max_try_time');
                    $bot->error_log = 'Proxy của tài khoản bị die. Tài khoản bị dừng chạy lúc ' . date("d/m/Y H:i:s") . '';
                    $bot->save();

                    Log::error("Bot ID " . $bot->id . " Proxy bị die!");
                    return;
                }
                $checkProxy = checkProxy($bot->proxy);
                $tryTestProxy++;
            } while ($checkProxy == false);
        }

        $fbDtg = getFbDtsg($bot->cookie, $bot->proxy);
        if (!$fbDtg) {
            $bot->count_error = 10;
            $bot->error_log = 'Đăng nhập không thành công do Cookie die. Tài khoản bị dừng chạy lúc ' . date("d/m/Y H:i:s") . '';
            $bot->save();

            Log::error("Bot ID " . $bot->id . " Đăng nhập không thành công!");
            return;
        }

        // Chọn $postId theo quy tắc đã setup + kiểm tra xem $postId đã từng tồn tại trong DB chưa

        $ignoreFBPostIds = BotLog::where('bot_id', $bot->id)->pluck('post_id')->toArray();
        $postId = $this->postId;
        if ($postId == '' || in_array($postId, $ignoreFBPostIds)) {
            $tryFindPost = 0;
            $newsFeedIsEmpty = true;
            do {
                $tryFindPost++;
                if ($tryFindPost > 3) {
                    if ($newsFeedIsEmpty) {
                        $bot->error_log = 'Đọc news feed ko có bài viết nào';
                        $bot->next_comment_time = $bot->next_comment_time + config('bot.try_news_feed_after') * 60;
                        $bot->next_reaction_time = $bot->next_comment_time + config('bot.try_news_feed_after') * 60;
                        /* Nếu muốn bot dừng hẳn ko chạy lại nữa thì dùng đoạn code này
                        $bot->count_error = $bot->count_error + 1;
                        if ($bot->count_error >= config('bot.max_try_time')) {
                            $bot->error_log = 'Đọc news feed ko có bài viết nào. BOT này đã bị lỗi quá '.config('bot.max_try_time').' lần nên dừng luôn.';
                        }
                        */
                        $bot->save();
                        return;
                    } else {
                        $bot->error_log = 'News feed có bài nhưng tương tác hết rồi :( Để chạy lại sau ' . config('bot.try_news_feed_after') . ' phút';
                        $bot->next_comment_time = $bot->next_comment_time + config('bot.try_news_feed_after') * 60;
                        $bot->next_reaction_time = $bot->next_comment_time + config('bot.try_news_feed_after') * 60;
                        $bot->save();
                        return;
                    }
                }
                $ignoreFbIds = explode("\n", str_replace("\r", "", $bot->black_list));
                $posts = getPostsFromNewFeed2($bot->cookie, $bot->proxy, $bot->bot_target, $ignoreFbIds, $ignoreFBPostIds);
                if (is_array($posts) && !empty($posts)) {
                    $newsFeedIsEmpty = false;
                    $post = $posts[rand(0, count($posts) - 1)];
                    $postId = $post->post_id;

                }
            } while ($postId == '' || in_array($postId, $ignoreFBPostIds));
        }


//        // Lấy thời gian bắt đầu và kết thúc của bot
//        if ($bot->start_time > $bot->end_time) {
//            // Trường hợp chạy đêm, ví dụ 23h hôm trc tới 8h sáng hôm sau
//            $start_time = strtotime('yesterday +' . $bot->start_time . 'hours');
//            $end_time = strtotime('today +' . $bot->end_time . 'hours');
//        } else {
//            $start_time = strtotime('today +' . $bot->start_time . 'hours');
//            $end_time = strtotime('today +' . $bot->end_time . 'hours');
//        }

        // Nếu bật Reaction
        if ($bot->reaction_on && ($bot->next_reaction_time <= time() || $this->postId != '')) {
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
            }

            // Lần reaction tiếp theo
            $bot->next_reaction_time = time() + $bot->reaction_frequency * rand(75, 125) / 100 * 60;
            $tempHour = date("H", $bot->next_reaction_time);
            $hours = json_decode($bot->run_time);
            if (!empty($hours)) {
                // Nếu next_reaction_time ko thỏa mãn 1 trong các múi giờ thì phải chọn giờ lớn hơn gần nhất
                if (!in_array($tempHour, $hours)) {
                    $foundTime = false;
                    foreach ($hours as $hour) {
                        if ($hour > $tempHour) {
                            $time = Carbon::now();
                            $bot->next_reaction_time = Carbon::create($time->year, $time->month, $time->day, $hour, rand(0, 5), rand(0, 30))->timestamp;
                            $foundTime = true;
                            break;
                        }
                    }

                    // Nếu tới đây vẫn ko tìm đc giờ phù hợp thì để ngày mai chạy ngay múi giờ đầu tiên
                    if (!$foundTime) {
                        $time = Carbon::tomorrow();
                        $bot->next_reaction_time = Carbon::create($time->year, $time->month, $time->day, $hours[0], rand(0, 5), rand(0, 30))->timestamp;
                    }
                }
            }
        }

        // Nếu bật Auto comment
        if ($bot->comment_on && ($bot->next_comment_time <= time() || $this->postId != '')) {
            // Random Sticker ID nếu người dùng có chọn collection
            $stickerId = null;
            if (!empty($bot->comment_sticker_collection)) {
                $tmpStickerId = randomStickerOfCollection($bot->cookie, $fbDtg, $bot->comment_sticker_collection, $bot->proxy);
                if ($tmpStickerId !== false) {
                    $stickerId = $tmpStickerId;
                }
            }

            // Lấy tên chủ post
            $postOwnerName = getPostOwner($bot->cookie, $bot->proxy, $postId);

            // Post ảnh
            $photoId = null;
            if ($stickerId == null && !empty($bot->comment_image_url) || filter_var($bot->comment_image_url, FILTER_VALIDATE_URL)) {
                $photoUrls = explode("\n", $bot->comment_image_url);
                // Set mặc định 1 cái ảnh
                $commentPhoto = "https://www.nicepng.com/png/detail/47-476266_free-png-3d-facebook-logo-png-icon-png.png";
                if (count($photoUrls) > 0) {
                    $commentPhoto = $photoUrls[rand(0, count($photoUrls) - 1)];
                }
                $photoId = uploadImageToFacebook($commentPhoto, $bot->cookie, $fbDtg, $postOwnerName, $bot->proxy);
            }

            // Build nội dung comment
            $commentContent = '';
            $comments = explode("\n", $bot->comment_content);
            if (count($comments) > 0) {
                $commentContent = DoShortCode($comments[rand(0, count($comments) - 1)], array('name' => $postOwnerName));
            }
            if (empty($commentContent)) {
                $commentContent = RandomComment();
            }

            // Gửi comment
            $comment = commentPostByCookie($bot->cookie, $fbDtg, $postId, $commentContent, $stickerId, $photoId, $bot->proxy);
            if ($comment) {
                $botLog = new BotLog();
                $botLog->bot_id = $bot->id;
                $botLog->action = 'COMMENT';
                $botLog->comment_id = $comment;
                $botLog->comment_content = $commentContent;
                $botLog->post_id = $postId;
                $botLog->save();
            }

            // Lần comment tiếp theo
            $bot->next_comment_time = time() + $bot->comment_frequency * rand(75, 125) / 100 * 60;
            $tempHour = date("H", $bot->next_comment_time);
            $hours = json_decode($bot->run_time);
            if (!empty($hours)) {
                // Nếu next_comment_time ko thỏa mãn 1 trong các múi giờ thì phải chọn giờ lớn hơn gần nhất
                if (!in_array($tempHour, $hours)) {
                    $foundTime = false;
                    foreach ($hours as $hour) {
                        if ($hour > $tempHour) {
                            $time = Carbon::now();
                            $bot->next_comment_time = Carbon::create($time->year, $time->month, $time->day, $hour, rand(0, 5), rand(0, 30))->timestamp;
                            $foundTime = true;
                            break;
                        }
                    }

                    // Nếu tới đây vẫn ko tìm đc giờ phù hợp thì để ngày mai chạy ngay múi giờ đầu tiên
                    if (!$foundTime) {
                        $time = Carbon::tomorrow();
                        $bot->next_comment_time = Carbon::create($time->year, $time->month, $time->day, $hours[0], rand(0, 5), rand(0, 30))->timestamp;
                    }
                }
            }
        }

        $bot->save();
    }
}
