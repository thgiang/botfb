<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bot;
use App\Models\BotLog;
use App\Models\WhiteListIds;
use App\Models\WhiteGroupIds;
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
            return response()->json([
                'status' => 'error', 'message' => $validator->errors()->first(), 'errors' => [$validator->getMessageBag()->toArray()]]);
        }


        // Kiểm tra proxy hoạt động ok không
        $tryTestProxy = 0;
        do {
            if ($tryTestProxy >= 3) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Proxy không hoạt động, vui lòng kiểm tra lại!'
                ]);
            }
            $checkProxy = checkProxy($request->proxy);
            $tryTestProxy++;
        } while ($checkProxy == false);

        // TODO Kiểm tra cookie hoạt động OK không


        // Xóa kí tự \r ở dấu xuống dòng
        if ($request->comment_content) {
            $request['comment_content'] = str_replace("\r", '', $request->comment_content);
        }

        // Chuyển run_time thành array :D
        $request['run_time'] = '[' . $request->run_time . ']';

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

            foreach ($bot as $key => $value) {
                if (isset($request->{$key})) {
                    $bot->{$key} = $request->{$key};
                }
            }
            $bot->count_error = 0;
            $bot->save();

            WhiteListIds::where('bot_id', $request->bot_id)->delete();
            WhiteGroupIds::where('bot_id', $request->bot_id)->delete();
        }

        if ($bot) {
            // Lưu danh sách white list nếu có
            if ($request->white_list) {
                $request->white_list = str_replace("\r", '', $request->white_list);
                $fbIds = explode("\n", $request->white_list);
                foreach ($fbIds as $fbId) {
                    $newWhiteList = new WhiteListIds();
                    $newWhiteList->bot_id = $bot->id;
                    $newWhiteList->fb_id = $fbId;
                    $newWhiteList->save();
                }
            }

            // Lưu danh sách white group nếu có
            if ($request->white_group) {
                $request->white_group = str_replace("\r", '', $request->white_group);
                $fbIds = explode("\n", $request->white_group);
                foreach ($fbIds as $fbId) {
                    $newWhiteGroup = new WhiteGroupIds();
                    $newWhiteGroup->bot_id = $bot->id;
                    $newWhiteGroup->fb_id = $fbId;
                    $newWhiteGroup->save();
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Lưu bot thành công!',
                'data' => $bot
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Lưu bot thất bại!'
        ]);
    }

    public function index(Request $request)
    {
        if (isset($request->bot_id)) {
            $bots = Bot::where('id', $request->bot_id)->paginate(10);
        } else {
            $bots = Bot::paginate(10);
        }
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

    public function logs(Request $request)
    {
        if (isset($request->bot_id)) {
            $botLogs = BotLog::where('bot_id', $request->bot_id)->orWhere('facebook_uid', $request->bot_id)->orderBy('updated_at', 'DESC')->paginate(10);
        } else {
            $botLogs = BotLog::orderBy('updated_at', 'DESC')->paginate(10);
        }

        if ($botLogs) {
            return response()->json([
                'status' => 'success',
                'message' => 'Lấy lịch sử thành công!',
                'data' => $botLogs
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Lấy lịch sử thất bại!'
        ]);
    }

    public function checkLiveCookie(Request $request)
    {
        $cookie = $request->cookie;
        $proxy = getTinsoftProxy();

        // Nếu lấy proxy thành công thì check xem cookie có sống không
        if ($proxy != false) {
            $checkAccount = getBasicInfoFromCookie($cookie, $proxy);
            if ($checkAccount != false) {
                return response()->json([
                    'status' => 'success',
                    'message' => $checkAccount
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Cookie không live'
        ]);
    }
}
