<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bot;
use App\Models\BotLog;
use App\Models\WhiteListIds;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BotController extends Controller
{
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cookie' => 'required',
            'name' => 'required',
            'proxy' => 'required',
            'comment_on' => 'required|boolean',
            'reaction_on' => 'required|boolean',
            'run_time' => 'required',
            'reaction_type' => 'required|numeric',
            'bot_target' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first(), 'errors' => [$validator->getMessageBag()->toArray()]]);
        }


        // Kiểm tra proxy hoạt động ok không
        $tryTestProxy = 0;
        do {
            if ($tryTestProxy >= 3) {
                return response()->json(['status' => 'error', 'message' => 'Proxy không hoạt động, vui lòng kiểm tra lại!']);
            }
            $checkProxy = checkProxy($request->proxy);
            $tryTestProxy++;
        } while ($checkProxy == false);

        // TODO Kiểm tra cookie hoạt động OK không


        // Xóa kí tự \r ở dấu xuống dòng
        if ($request->comment_content) {
            $request->comment_content = str_replace("\r", '', $request->comment_content);
        }

        // Chuyển run_time thành array :D
        $request->run_time = '[' . $request->run_time . ']';

        // Lưu bot
        if (!$request->bot_id) {
            // Nếu là thêm mới, phải check thêm bot này đã từng được thêm chưa
            preg_match("/c_user=([0-9]+);/", $request->cookie, $fbBotID);
            if (isset($fbBotID[1])) {
                $fbBotID = $fbBotID[1];
                $bot = Bot::where('facebook_uid', $fbBotID)->orWhere('cookie', $request->cookie)->first();
                if ($bot) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Cookie bạn vừa nhập đã tồn tại trong hệ thống, vui lòng kiểm tra lại!'
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cookie không hợp lệ: Thiếu "c_user", vui lòng thử lại!'
                ]);
            }

            $bot = Bot::create($request->all() + ['facebook_uid' => $fbBotID]);
        } else {
            $bot = Bot::where('id', $request->bot_id)->first();
            if (!$bot) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Không tìm thấy bot có ID ' . $request->bot_id . ' để update, vui lòng thử lại!'
                ]);
            }

            $bot->cookie = isset($request->cookie) ? $request->cookie : $bot->cookie;
            $bot->name = isset($request->name) ? $request->name : $bot->name;
            $bot->proxy = isset($request->proxy) ? $request->proxy : $bot->proxy;
            $bot->bot_target = isset($request->bot_target) ? $request->bot_target : $bot->bot_target;
            $bot->reaction_on = isset($request->reaction_on) ? $request->reaction_on : $bot->reaction_on;
            $bot->reaction_frequency = isset($request->reaction_frequency) ? $request->reaction_frequency : $bot->reaction_frequency;
            $bot->reaction_type = isset($request->reaction_type) ? $request->reaction_type : $bot->reaction_type;
            $bot->comment_on = isset($request->comment_on) ? $request->comment_on : $bot->comment_on;
            $bot->comment_frequency = isset($request->comment_frequency) ? $request->comment_frequency : $bot->comment_frequency;
            $bot->comment_image_url = isset($request->comment_image_url) ? $request->comment_image_url : $bot->comment_image_url;
            $bot->comment_sticker_collection = isset($request->comment_sticker_collection) ? $request->comment_sticker_collection : $bot->comment_sticker_collection;
            $bot->comment_content = isset($request->comment_content) ? $request->comment_content : $bot->comment_content;
            $bot->run_time = isset($request->run_time) ? $request->run_time : $bot->run_time;
            $bot->black_list = isset($request->black_list) ? $request->black_list : $bot->black_list;
            $bot->white_list = isset($request->white_list) ? $request->white_list : $bot->white_list;

            $bot->count_error = 0;

            $bot->save();
        }

        // Lưu danh sách white list nếu có
        if ($request->white_list) {
            $request->white_list = str_replace("\r", '', $request->white_list);
            if ($request->bot_id) {
                // Nếu update bot đã có từ trc thì xóa hết white list cũ đi để thêm lại
                WhiteListIds::where('bot_id', $request->bot_id)->delete();
            }
            $fbIds = explode("\n", $request->white_list);
            foreach ($fbIds as $fbId) {
                $newWhiteList = new WhiteListIds();
                $newWhiteList->bot_id = $bot->id;
                $newWhiteList->fb_id = $fbId;
                $newWhiteList->save();
            }
        }

        if ($bot) {
            return response()->json([
                'status' => 'success',
                'message' => 'Lưu bot thành công, ID: ' . $bot->id,
                'data' => $bot
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Lưu bot thất bại'
        ]);
    }

    public function index(Request $request)
    {
        $bots = Bot::paginate(10);
        if ($bots) {
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy danh sách bots thành công!',
                'data' => $bots
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Lấy danh sách bots thất bại!'
        ]);
    }

    public function delete(Request $request)
    {
        $bot = Bot::where('id', $request->id)->delete();
        if ($bot) {
            return response()->json([
                'status' => 'success',
                'message' => "Đã xóa bot ID " . $request->id
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => "Không tìm thấy bot " . $request->id
        ]);
    }

    public function logs()
    {
        $botLogs = BotLog::orderBy('updated_at', 'DESC')->paginate(10);

        if ($botLogs) {
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy lịch sử bots thành công!',
                'data' => $botLogs
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Lấy lịch sử thất bại!'
        ]);
    }
}
