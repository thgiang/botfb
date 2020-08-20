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
use App\Helpers\ZHelper;

class BotFacebook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	
	public $timeout = 50;
	
    private $botId;
    private $postId;
    private $requestSource;
    private $extraData;

    /**
     * Create a new job instance.
     * $requestSource là nguồn gốc Bot này bị gọi từ đâu. Hiện có 3 nguồn là (white_list, white_group) (nếu vào đc tới đây sẽ bỏ qua điều kiện hẹn giờ) và hẹn giờ thông thường
     *
     * @param int $botId
     * @param string $postId
     * @param string $requestSource
     */
    public function __construct($botId, $postId = '', $requestSource = 'news_feed', $extraData = array())
    {
        $this->botId = $botId;
        $this->postId = $postId;
        $this->requestSource = $requestSource;
        $this->extraData = (array)$extraData;
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
        } else if ($bot->count_error >= config('bot.max_try_time')) {
            $bot->touch();
            return;
        }

        // Nếu lệnh từ white_list hoặc white_group thì dùng setting riêng
        $comment_on = $bot->comment_on;
        $reaction_on = $bot->reaction_on;
        if ($this->requestSource == 'white_list') {
            $comment_on = $bot->white_list_comment_on;
            $reaction_on = $bot->white_list_reaction_on;
        } else if ($this->requestSource == 'white_group') {
            $comment_on = $bot->white_group_comment_on;
            $reaction_on = $bot->white_group_reaction_on;
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

        $ignoreFBPostIds = BotLog::where('bot_fid', $bot->facebook_uid)->pluck('post_id')->toArray();
        $ignoreFbIds = explode("\n", str_replace("\r", "", $bot->black_list));
        $postId = $this->postId;

        // Nếu white_list, white_group đẩy ID xuống bắt like nhưng thằng này nằm trong blacklist thì cũng nghỉ
        if (!empty($this->extraData['owner_id']) && in_array($this->extraData['owner_id'], $ignoreFbIds)) {
            return;
        }

        // Nếu chưa biết phải tương tác với bài nào thì đi tìm, còn white_list và white_group thì nó truyền hẳn ID xuống r
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

                $posts = getPostsFromNewFeed2($bot->cookie, $bot->proxy, $bot->bot_target, $ignoreFbIds, $ignoreFBPostIds);
                if (is_array($posts) && !empty($posts)) {
                    $newsFeedIsEmpty = false;
					// $post = $posts[rand(0, count($posts) - 1)];
					// Éo hiểu sao thi thoảng lỗi  Undefined offset: 0 nên phải thêm mấy dòng code bên dưới
					$randomIndex = rand(0, count($posts) - 1);
					if (isset($posts[$randomIndex]) && !empty($posts[$randomIndex])) {
						$post = $posts[$randomIndex];
						$postId = $post->post_id;
					} else {
						foreach ($posts AS $post) {
							if (!empty($post->id)) {
								$postId = $post->post_id;
							}
						}
					}
                }
            } while ($postId == '' || in_array($postId, $ignoreFBPostIds));
        }

        // TODO: Nếu bài post sắp tương tác thuộc white_list hoặc group chưa bài đó thuộc white_group thì phải lấy setting theo white_??_comment_on, white_??_reaction_on

        // Nếu bật Reaction
        if ($reaction_on && ($bot->next_comment_time <= time() || $this->requestSource == 'white_list' || $this->requestSource == 'white_group')) {
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
                $botLog->bot_fid = $bot->facebook_uid;
                $botLog->action = $reactionType;
                $botLog->post_id = $postId;
                $botLog->request_source = $this->requestSource;
                $botLog->save();
            }

            // Lần reaction tiếp theo
            $bot->next_reaction_time = time() + $bot->reaction_frequency * rand(75, 125) / 100 * 60;
            $hours = @json_decode($bot->run_time);
            if ($hours && !empty($hours) && is_array($hours)) {
                $bot->next_reaction_time = ZHelper::NearestTime($bot->next_reaction_time, $hours);
            }
        }

        // Nếu bật Auto comment
        if ($comment_on && ($bot->next_comment_time <= time() || $this->requestSource == 'white_list' || $this->requestSource == 'white_group')) {
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
                $textWriteOnImage = null;
                if (count($photoUrls) > 0) {
                    $commentPhoto = $photoUrls[rand(0, count($photoUrls) - 1)];
                }
                if ($bot->write_post_owner_name_to_image) {
                    $textWriteOnImage = $postOwnerName;
                }
                $photoId = uploadImageToFacebook($commentPhoto, $bot->cookie, $fbDtg, $textWriteOnImage, $bot->proxy);
            }

            // Build nội dung comment
            $commentContent = '';
            $comments = explode("\n", $bot->comment_content);
            if (count($comments) > 0) {
                $commentContent = DoShortCode($comments[rand(0, count($comments) - 1)], array('name' => $postOwnerName));
            }
            if (empty($commentContent) && empty($photoId) && empty($stickerId)) {
                $bot->error_log = 'Bạn ko đc để trống cả 3 thứ: Nội dung comment, ảnh và sticker';
                $bot->next_comment_time = $bot->next_comment_time + config('bot.try_news_feed_after') * 60;;
                $bot->next_reaction_time = $bot->next_reaction_time + config('bot.try_news_feed_after') * 60;;
                $bot->count_error++;
                $bot->save();
                return;
                //$commentContent = RandomComment();
            }

            // Gửi comment
            $comment = commentPostByCookie($bot->cookie, $fbDtg, $postId, $commentContent, $stickerId, $photoId, $bot->proxy);
            if ($comment) {
                $botLog = new BotLog();
                $botLog->bot_id = $bot->id;
                $botLog->bot_fid = $bot->facebook_uid;
                $botLog->action = 'COMMENT';
                $botLog->comment_id = $comment;
                $botLog->comment_content = $commentContent;
                $botLog->sticker_id = $stickerId;
                $botLog->request_source = $this->requestSource;
                $botLog->post_id = $postId;
                $botLog->save();
            }

            // Lần comment tiếp theo
            $bot->next_comment_time = time() + $bot->comment_frequency * rand(75, 125) / 100 * 60;
            $hours = @json_decode($bot->run_time);
            if ($hours && !empty($hours) && is_array($hours)) {
                $bot->next_comment_time = ZHelper::NearestTime($bot->next_comment_time, $hours);
            }
        }

        $bot->save();
    }
}
