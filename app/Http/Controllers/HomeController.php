<?php

namespace App\Http\Controllers;

use App\Jobs\BotFacebook;
use App\Models\Bot;
use App\Models\BotLog;
use Illuminate\Http\Request;
use App\Models\WhiteGroupIds;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function testJob() {
		/*
		$this->fb_id = '2139567299590802';
		// Lấy 1 con bot đang coi group này là white_group với điều kiện nó phải join rồi để quét đc bài mới
        $whiteIds = WhiteGroupIds::where('fb_id', $this->fb_id)->get();

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
					echo 'Đã join';
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
            echo ("WARNING: Đang quét bài mới của group " . $this->fb_id . " nhưng ko có bot nào quét đc bài viết của group này. \n\nLogs: " . $logs);
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
					echo 'Giờ comment tiếp theo: ';
					echo date('d/m/Y H:i:s', $bot->next_comment_time);
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
                    echo 'Gọi BOT tương tác: '. $bot->id .' với bài viết '.$post->post_id;
					echo '<br>';
					echo 'Chi tiết bài viết: ';
					echo '<br>';
					print_r($post);
				
					BotFacebook::dispatch($bot->id, $post->post_id, 'white_group', (array)$post);
                    $countPost++;
                }
            }
        }
		*/
		$bot = Bot::where('id', 25)->first();
		 $posts = getPostsFromNewFeed2($bot->cookie, $bot->proxy);
		 
		 $comments = explode("\n", $bot->comment_content);
            if (count($comments) > 0) {
				$noidung = $comments[rand(0, count($comments) - 1)];
				echo $noidung.'<br>';
                $commentContent = DoShortCode($noidung, array('name' => 'Giang'));
            }
			echo $commentContent;
			
			exit();
    }
}
