<?php

namespace App\Jobs;

use App\Http\Controllers\Api\ProxyController;
use App\Models\Bot;
use App\Models\BotLog;
use App\Models\BotTrace;
use App\Models\SystemToken;
use App\Models\WhiteGroupId;
use App\Models\WhiteListId;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Helpers\ZHelper;

class BotFacebookV2 implements ShouldQueue
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
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bot = Bot::where('id', $this->botId)->first();
        if (!$bot || $bot->count_error >= config('bot.max_try_time')) {
            return;
        }

        $traceCode = strtoupper(ZHelper::RandomString(5));
        $bot->trace_code = $traceCode;
        $bot->save();

// ƯU TIÊN SỐ 1: white_list
        if ($bot->white_list_run_mode == BOT_WHITE_MODE_ASAP && !empty($bot->white_list)) {
            BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Rơi vào trường hợp BOT_SOURCE_WHITE_LIST_ASAP');
            return $this->BotWhiteList($bot, BOT_SOURCE_WHITE_LIST_ASAP);
        }

// ƯU TIÊN SỐ 2: white_group
        if ($bot->white_group_run_mode == BOT_WHITE_MODE_ASAP && !empty($bot->white_group)) {
            BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Rơi vào trường hợp BOT_SOURCE_WHITE_GROUP_ASAP');
            return $this->BotWhiteGroup($bot, BOT_SOURCE_WHITE_GROUP_ASAP);
        }

// ƯU TIÊN SỐ 3: Đến giờ hoạt động theo lịch bt, random chọn 1 trong 3 nơi là news feed, white_list, white_group
        $targetCount = 0;
        if (!empty($bot->white_list)) {
            $targetCount++;
        }
        if (!empty($bot->white_group)) {
            $targetCount++;
        }
        // Random chọn 1 trong 3 kiểu tương tác
        $action = rand(0, $targetCount);

        if ($action == 0) {
            BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Rơi vào trường hợp BOT_SOURCE_NORMAL');
            return $this->BotNewsFeed($bot);
        } else if ($action == 1) {
            BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Rơi vào trường hợp BOT_SOURCE_WHITE_LIST_MIXED');
            return $this->BotWhiteList($bot, BOT_SOURCE_WHITE_LIST_MIXED);
        } else if ($action == 2) {
            BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Rơi vào trường hợp BOT_SOURCE_WHITE_GROUP_MIXED');
            return $this->BotWhiteGroup($bot, BOT_SOURCE_WHITE_GROUP_MIXED);
        }
    }

    public function BotNewsFeed(Bot $bot)
    {
        $postId = '';
        $tryFindPost = 0;
        $newsFeedIsEmpty = true;

        BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Tìm BOT trên news feed');

        $ignoreFbIds = explode("\n", str_replace("\r", "", $bot->black_list));
        $ignoreFBPostIds = BotLog::where('bot_fid', $bot->facebook_uid)->limit(100)->pluck('post_id')->toArray();
        do {
            $tryFindPost++;
            if ($tryFindPost > 3) {
                if ($newsFeedIsEmpty) {
                    $bot->error_log = 'Đọc news feed ko có bài viết nào';
                    $bot->next_comment_time = $bot->next_comment_time + config('bot.try_news_feed_after') * 60;
                    $bot->next_reaction_time = $bot->next_comment_time + config('bot.try_news_feed_after') * 60;
                    $bot->save();
                    BotTrace::SaveTrace($bot->trace_code, false, $bot->id, $bot->facebook_uid, 'Đọc news feed ko có bài viết nào');
                    return false;
                } else {
                    $bot->error_log = 'News feed có bài nhưng tương tác hết rồi :( Để chạy lại sau ' . config('bot.try_news_feed_after') . ' phút';
                    $bot->next_comment_time = $bot->next_comment_time + config('bot.try_news_feed_after') * 60;
                    $bot->next_reaction_time = $bot->next_comment_time + config('bot.try_news_feed_after') * 60;
                    $bot->save();
                    BotTrace::SaveTrace($bot->trace_code, false, $bot->id, $bot->facebook_uid, 'News feed có bài nhưng tương tác hết rồi :( Để chạy lại sau ' . config('bot.try_news_feed_after') . ' phút');
                    return false;
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
                    BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Tìm thấy post để tương tác', array('post_id' => $postId));
                } else {
                    foreach ($posts as $post) {
                        if (!empty($post->id)) {
                            $postId = $post->post_id;
                            BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Tìm thấy post để tương tác', array('post_id' => $postId));
                        }
                    }
                }
            }
        } while ($postId == '' || in_array($postId, $ignoreFBPostIds));

        return $this->BotFocusToSpecialPost($bot, $postId, BOT_SOURCE_NORMAL);
    }

    public function BotWhiteList(Bot $bot, $mode = BOT_SOURCE_WHITE_LIST_ASAP)
    {
        $whiteIds = WhiteListId::where('bot_id', $this->botId)->orderBy('last_run_time', 'ASC')->get();
        if (!$whiteIds) {
            $oldBotWhiteList = $bot->white_list;
            $bot->white_list = null;
            $bot->save();
            BotTrace::SaveTrace($bot->trace_code, false, $bot->id, $bot->facebook_uid, 'Giá trị white_list ko đúng, đã update về null', array('old_value' => $oldBotWhiteList));
            return false;
        }

        foreach ($whiteIds as $whiteId) {
            // Nếu FB này vừa có bài mới cách đây chưa tới 60s thì tương tác ngay thôi!
            if (time() - $whiteId->latest_post_time < 60) {
                $history = BotLog::where('bot_fid', $bot->facebook_uid)->where('post_id', $whiteId->latest_post_fid)->first();
                if (!$history) {
                    BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Bot khác đã tìm sẵn đc bài vừa mới đăng nên tương tác luôn', array('fb_post_id' => $whiteId->latest_post_fid));
                    $whiteId->last_run_time = time();
                    $whiteId->save();
                    return $this->BotFocusToSpecialPost($bot, $whiteId->latest_post_fid, $mode);
                }
            }

            // Lấy bài mới trên trang cá nhân của người này
            $postId = $this->GetPostFromUid($whiteId->fb_id);
            if ($postId > 0) {
                // Lưu lại bài mới nhất để BOT khác đỡ phải quét lại
                $tmps = WhiteListId::where('fb_id', $whiteId->fb_id)->get();
                foreach ($tmps as $tmp) {
                    $tmp->latest_post_time = time();
                    $tmp->latest_post_fid = $postId;
                    $tmp->save();
                }

                // Kiểm tra xem đã tương tác hay chưa
                $history = BotLog::where('bot_fid', $bot->facebook_uid)->where('post_id', $postId)->first();
                if (!$history) {
                    BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Đã tìm đc bài trong white_list cần tương tác', array('fb_id' => $whiteId->fb_id, 'fb_post_id' => $postId));
                    // Tương tác ngay lập tức với $postId này, sử dụng các option của white_list
                    $whiteId->last_run_time = time();
                    $whiteId->save();
                    return $this->BotFocusToSpecialPost($bot, $postId, $mode);
                }
            }
        }
    }

    public function BotWhiteGroup(Bot $bot, $mode = BOT_SOURCE_WHITE_GROUP_ASAP)
    {
        $whiteIds = WhiteGroupId::where('bot_id', $this->botId)->orderBy('last_run_time', 'ASC')->get();
        if (!$whiteIds) {
            $oldBotWhiteGroup = $bot->white_group;
            $bot->white_group = null;
            $bot->save();
            BotTrace::SaveTrace($bot->trace_code, false, $bot->id, $bot->facebook_uid, 'Giá trị white_group ko đúng, đã update về null', array('old_value' => $oldBotWhiteGroup));
            return false;
        }
        $ignoreFbIds = explode("\n", str_replace("\r", "", $bot->black_list));

        foreach ($whiteIds AS $whiteId) {
            // Kiểm tra xem đã join group chưa
            if (!checkCookieJoinedGroup($bot->cookie, $whiteId->fb_id, $bot->proxy)) {
                BotTrace::SaveTrace($bot->trace_code, false, $bot->id, $bot->facebook_uid, 'Chưa join group', array('bot_id' => $whiteId->bot_id, 'bot_facebook_uid' => $bot->facebook_uid, 'group_fb_id' => $whiteId->fb_id));
                continue;
            } else {
                // Nếu group FB này vừa có bài mới cách đây chưa tới 60s thì tương tác ngay thôi!
                if (time() - $whiteId->latest_post_time < 60) {
                    $history = BotLog::where('bot_fid', $bot->facebook_uid)->where('post_id', $whiteId->latest_post_fid)->first();
                    if (!$history) {
                        BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Bot khác đã tìm sẵn đc bài vừa mới đăng nên tương tác luôn', array('fb_post_id' => $whiteId->latest_post_fid));
                        $whiteId->last_run_time = time();
                        $whiteId->save();
                        return $this->BotFocusToSpecialPost($bot, $whiteId->latest_post_fid, $mode);
                    }
                }

                // Nếu ko có bài đc quét sẵn thì phải đi tìm thôi
                BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Đã join group, bắt đầu quét bài', array('bot_id' => $whiteId->bot_id, 'bot_facebook_uid' => $bot->facebook_uid, 'group_fb_id' => $whiteId->fb_id));
                $posts = getPostsFromGroup($bot->cookie, $whiteId->fb_id, $bot->proxy);
                if (isset($posts[0])) {
                    // Lưu lại bài mới nhất để BOT khác đỡ phải quét lại
                    $tmps = WhiteGroupId::where('fb_id', $whiteId->fb_id)->get();
                    foreach ($tmps as $tmp) {
                        $tmp->latest_post_time = time();
                        $tmp->latest_post_fid = $posts[0]->post_id;
                        $tmp->save();
                    }
                } else {
                    BotTrace::SaveTrace($bot->trace_code, false, $bot->id, $bot->facebook_uid, 'Không quét đc bài viết nào trong group cả. getPostsFromGroup($cookie, $fb_id, $proxy)', array('cookie' => $bot->cookie, 'fb_id' => $whiteId->fb_id, 'proxy' => $bot->proxy));
                    return false;
                }

                foreach ($posts as $post) {
                    if (!in_array($post->owner_id, $ignoreFbIds)) {
                        $history = BotLog::where('bot_fid', $bot->facebook_uid)->where('post_id', $post->post_id)->first();
                        if (!$history) {
                            BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Đã tìm đc bài trong white_group cần tương tác', array('fb_id' => $whiteId->fb_id, 'fb_post_id' => $post->post_id));
                            $whiteId->last_run_time = time();
                            $whiteId->save();
                            return $this->BotFocusToSpecialPost($bot, $post->post_id, $mode);
                        } else {
                            BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Đã tìm đc bài nhưng bài này tương tác mất rồi', array());
                        }
                    }
                }
            }
        }
    }

    /**
     * Lấy bài từ trang cá nhân của 1 người nào đó
     * @param string $fid Id của Facebook cần quét
     * @return int 0|postId
     */
    public function GetPostFromUid($fid)
    {
        $token = SystemToken::where('is_live', true)->inRandomOrder()->first();
        if (!$token) {
            sendMessageTelegram("LỖI TO: KHÔNG CÒN TOKEN ĐỂ CRAWL BÀI VIẾT");
            Log::error('LỖI TO: KHÔNG CÒN TOKEN ĐỂ CRAWL BÀI VIẾT');
            return 0;
        }

        // Lấy proxy để request
        $proxy = getTinsoftProxy();
        if ($proxy == false) {
            sendMessageTelegram("WARNING: Hàm lấy proxy Tinsoft ko trả về đc proxy nên bot phải chờ tới lần chạy sau");
            Log::error('WARNING: Hàm lấy proxy Tinsoft ko trả về đc proxy nên bot phải chờ tới lần chạy sau');
            return 0;
        }

        // Lấy thông tin token
        $tokenInfo = FacebookGet('me', array(), $token->token, $proxy);
        if (empty($tokenInfo) || empty($tokenInfo->id)) {
            $token->is_live = false;
            $token->save();

            $countLiveToken = SystemToken::where('is_live', true)->count();
            sendMessageTelegram("WARNING: Thêm 1 token vừa die, hệ thống chỉ còn " . $countLiveToken . " token");
            Log::error("WARNING: Thêm 1 token vừa die, hệ thống chỉ còn " . $countLiveToken . " token");
            return 0;
        }

        // Token OK, lấy 2 bài gần nhất
        $feed = FacebookGet($fid . '/feed', array('limit' => config('bot.white_list_feed_limit')), $token->token, $proxy);
        if (empty($feed) || empty($feed->data)) {
            return 0;
        }

        foreach ($feed->data as $post) {
            $postId = str_replace($fid . '_', '', $post->id);
            return $postId;
        }
    }

    /**
     * @param Bot $bot
     * @param string $fbPostId
     * @param string $source
     * @return boolean
     */
    public function BotFocusToSpecialPost(Bot $bot, $fbPostId, $source = BOT_SOURCE_WHITE_LIST_ASAP)
    {
        $commentOn = false;
        $reactionOn = false;

        // Ktra xem cần làm hành động gì (comment, reaction)
        switch ($source) {
            case BOT_SOURCE_WHITE_LIST_ASAP:
                $commentOn = $bot->white_list_comment_on;
                $reactionOn = $bot->white_list_reaction_on;
                break;
            case BOT_SOURCE_WHITE_GROUP_ASAP:
                $commentOn = $bot->white_group_comment_on;
                $reactionOn = $bot->white_group_reaction_on;
                break;
            case BOT_SOURCE_WHITE_LIST_MIXED:
                if ($bot->next_comment_time <= time()) {
                    $commentOn = $bot->white_list_comment_on;
                }
                if ($bot->next_reaction_time <= time()) {
                    $reactionOn = $bot->white_list_reaction_on;
                }
                break;
            case BOT_SOURCE_WHITE_GROUP_MIXED:
                if ($bot->next_comment_time <= time()) {
                    $commentOn = $bot->white_group_comment_on;
                }
                if ($bot->next_reaction_time <= time()) {
                    $reactionOn = $bot->white_group_reaction_on;
                }
                break;
            default:
                if ($bot->next_comment_time <= time()) {
                    $commentOn = $bot->comment_on;
                }
                if ($bot->next_comment_time <= time()) {
                    $reactionOn = $bot->reaction_on;
                }
                break;
        }
        BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Bắt đầu chạy', array('comment_on' => $commentOn, 'reaction_on' => $reactionOn, 'fb_post_id' => $fbPostId));

        // Chuẩn bị proxy
        if (!$this->CheckBotProxy($bot)) {
            BotTrace::SaveTrace($bot->trace_code, false, $bot->id, $bot->facebook_uid, 'Proxy die, dừng bot');
            return false;
        }
        BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, ' Proxy OK');

        // Lấy FBDTG
        $fbDtg = getFbDtsg($bot->cookie, $bot->proxy);
        if (!$fbDtg) {
            $bot->count_error = 10;
            $bot->error_log = 'Đăng nhập không thành công do Cookie die. Tài khoản bị dừng chạy lúc ' . date("d/m/Y H:i:s") . '';
            $bot->save();
            BotTrace::SaveTrace($bot->trace_code, false, $bot->id, $bot->facebook_uid, 'Cookie die');
            return false;
        }
        BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Cookie OK');

        // Gửi reaction
        if ($reactionOn) {
            $reactions = array(1, 2, 3, 4, 6, 8, 16);
            if (in_array($bot->reaction_type, $reactions)) {
                $reactionType = $bot->reaction_type;
            } else {
                $reactionType = $reactions[rand(0, count($reactions) - 1)];
            }
            $reaction = reactionPostByCookie($bot->cookie, $fbDtg, $fbPostId, $reactionType, $bot->proxy);
            if ($reaction) {
                $botLog = new BotLog();
                $botLog->bot_id = $bot->id;
                $botLog->bot_fid = $bot->facebook_uid;
                $botLog->action = $reactionType;
                $botLog->post_id = $fbPostId;
                $botLog->request_source = $source;
                $botLog->save();
                // React đc thành công rồi thì xóa các lỗi cũ
                $bot->error_log = null;
                $bot->count_error = 0;
                BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Reaction thành công', array('reaction' => $reactionType, 'bot_log_id' => $botLog->id));
            } else {
                BotTrace::SaveTrace($bot->trace_code, false, $bot->id, $bot->facebook_uid, 'Gửi reaction bị lỗi', array('reaction' => $reactionType));
            }

            // Lần reaction tiếp theo
            $bot->next_reaction_time = time() + $bot->reaction_frequency * rand(75, 125) / 100 * 60;
            $hours = @json_decode($bot->run_time);
            if ($hours && !empty($hours) && is_array($hours)) {
                $bot->next_reaction_time = ZHelper::NearestTime($bot->next_reaction_time, $hours);
            }
        }

        // Gửi comment
        if ($commentOn) {
            // Random Sticker ID nếu người dùng có chọn collection
            $stickerId = null;
            if (!empty($bot->comment_sticker_collection)) {
                BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Khách setting comment có dùng sticker', array('comment_sticker_collection' => $bot->comment_sticker_collection));
                $tmpStickerId = randomStickerOfCollection($bot->cookie, $fbDtg, $bot->comment_sticker_collection, $bot->proxy);
                if ($tmpStickerId !== false) {
                    $stickerId = $tmpStickerId;
                    BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Lấy sticker thành công', array('comment_sticker_collection' => $bot->comment_sticker_collection, 'sticker_id' => $stickerId));
                } else {
                    BotTrace::SaveTrace($bot->trace_code, false, $bot->id, $bot->facebook_uid, 'Lấy sticker bị lỗi');
                }
            }

            // Lấy tên chủ post
            $postOwnerName = getPostOwner($bot->cookie, $bot->proxy, $fbPostId);
            BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Lấy xong tên chủ post', array('name' => $postOwnerName));

            // Post ảnh
            $photoId = null;
            if ($stickerId == null && !empty($bot->comment_image_url)) {
                $bot->comment_image_url = str_replace("\r", '', $bot->comment_image_url);
                $photoUrls = explode("\n", $bot->comment_image_url);
                $textWriteOnImage = null;
                $commentPhoto = '';
                if (count($photoUrls) > 0) {
                    $commentPhoto = $photoUrls[rand(0, count($photoUrls) - 1)];
                }
                if ($bot->write_post_owner_name_to_image) {
                    $textWriteOnImage = $postOwnerName;
                }
                if ($commentPhoto != '') {
                    BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Bắt đầu đăng ảnh', array('url' => $commentPhoto, 'text' => $textWriteOnImage));
                    $photoId = uploadImageToFacebook($commentPhoto, $bot->cookie, $fbDtg, $textWriteOnImage, $bot->proxy);
                    if ($photoId) {
                        BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Đăng ảnh thành công', array('url' => $commentPhoto, 'text' => $textWriteOnImage, 'photo_id' => $photoId));
                    } else {
                        BotTrace::SaveTrace($bot->trace_code, false, $bot->id, $bot->facebook_uid, 'Lỗi khi đăng ảnh');
                    }

                }
            }

            // Build nội dung comment
            $commentContent = '';
            $bot->comment_content = str_replace("\r", '', $bot->comment_content);
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
                BotTrace::SaveTrace($bot->trace_code, false, $bot->id, $bot->facebook_uid, 'Cả 3 thông tin sticker, photo và comment đều rỗng nên ko đăng comment đc');
                return false;
                //$commentContent = RandomComment();
            }

            $comment = commentPostByCookie($bot->cookie, $fbDtg, $fbPostId, $commentContent, $stickerId, $photoId, $bot->proxy);
            if ($comment) {
                $botLog = new BotLog();
                $botLog->bot_id = $bot->id;
                $botLog->bot_fid = $bot->facebook_uid;
                $botLog->action = 'COMMENT';
                $botLog->comment_id = $comment;
                $botLog->comment_content = $commentContent;
                $botLog->sticker_id = $stickerId;
                $botLog->request_source = $source;
                $botLog->post_id = $fbPostId;
                $botLog->save();
                BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Comment thành công', array('comment' => $comment, 'bot_log_id' => $botLog->id));
                // Comment đc thành công rồi thì xóa các lỗi cũ
                $bot->error_log = null;
                $bot->count_error = 0;
            } else {
                BotTrace::SaveTrace($bot->trace_code, true, $bot->id, $bot->facebook_uid, 'Comment lỗi');
            }

            // Lần comment tiếp theo
            $bot->next_comment_time = time() + $bot->comment_frequency * rand(75, 125) / 100 * 60;
            //$bot->next_comment_time = time() + $bot->comment_frequency * 60;
            $hours = @json_decode($bot->run_time);
            if ($hours && !empty($hours) && is_array($hours)) {
                $bot->next_comment_time = ZHelper::NearestTime($bot->next_comment_time, $hours);
            }
        }

        // Nếu BOT có tgian dãn cách like và comment bằng nhau, thì cho nó vừa like vừa comment cùng lúc
        if ($bot->comment_frequency == $bot->reaction_frequency) {
            $bot->next_comment_time = max($bot->next_reaction_time, $bot->next_reaction_time);
            $bot->next_reaction_time = $bot->next_comment_time;
        }

        $bot->save();
        return true;
    }

    public function CheckBotProxy(Bot $bot)
    {
        if (empty($bot->proxy) || count(explode(':', $bot->proxy)) !== 2) {
            $bot->proxy = null;
            $bot->save();
        } else {
            // Kiểm tra proxy hoạt động ok không
            $tryTestProxy = 0;
            do {
                if ($tryTestProxy >= 3) {
                    $proxyController = new ProxyController();
                    sendMessageTelegram('Proxy của tài khoản bị die, thay proxy mới lúc ' . date("d/m/Y H:i:s") . '');
                    $replaceProxy = $proxyController->replaceProxy($bot->proxy, $bot->id);

                    if ($replaceProxy == false) {
                        $bot->count_error = config('bot.max_try_time');
                        $bot->error_log = 'Proxy của tài khoản bị die, không lấy được proxy mới, tài khoản dừng chạy lúc ' . date("d/m/Y H:i:s") . '';
                        $bot->save();
                        sendMessageTelegram('Proxy của bot ' . $bot->id . ' bị die, yêu cầu thay mới nhưng kho hết proxy');
                        return true;
                    } else {
                        $bot->error_log = 'Proxy của tài khoản bị die, đã thay proxy mới lúc ' . date("d/m/Y H:i:s") . '';
                        $bot->proxy = $replaceProxy;
                        $bot->save();
                        return true;
                    }
                }
                $checkProxy = checkProxy($bot->proxy);
                $tryTestProxy++;
            } while ($checkProxy == false);
            return true;
        }
    }
}
