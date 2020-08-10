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
            'cookie' => 'required|unique:bots,cookie',
            'name' => 'required',
            'proxy' => 'required',
            'comment_on' => 'required|boolean',
            'reaction_on' => 'required|boolean',
            'start_time' => 'required|numeric|min:0|max:23',
            'end_time' => 'required|numeric|min:0|max:23',
            'reaction_type' => 'required|numeric',
            'bot_target' => 'required'
        ], [
            'cookie.unique' => 'Cookie bạn vừa nhập đã tồn tại trong hệ thống, vui lòng kiểm tra lại!'
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

        // Xóa kí tự \r ở dấu xuống dòng
        if ($request->comment_content) {
            $request->comment_content = str_replace("\r", '', $request->comment_content);
        }

        // Lưu bot
        if (!$request->bot_id) {
            $bot = Bot::create($request->all());
        } else {
            $bot = Bot::where('bot_id', $request->bot_id)->update($request->all());
        }

        // Lưu danh sách white list nếu có
        if ($request->white_list) {
            $request->white_list = str_replace("\r", '', $request->white_list);
            if ($request->bot_id) {
                // Nếu update bot đã có từ trc thì xóa hết white list cũ đi để thêm lại
                WhiteListIds::where('bot_id', $request->bot_id)->detele();
            }
            $fbIds = explode("\n", $request->white_list);
            foreach ($fbIds as $fbId) {
                $newWhiteList = new WhiteListIds();
                $newWhiteList->bot_id = $bot->id;
                $newWhiteList->fb_id = $fbId;
                $newWhiteList->save();
            }
        }

        return response()->json(['status' => 'success', 'data' => $bot, 'message' => 'Lưu bot thành công, ID: ' . $bot->id]);
    }

    public function index(Request $request)
    {
        return Bot::paginate(15);
    }

    public function delete(Request $request)
    {
        Bot::where('id', $request->id)->delete();
        return "Đã xóa bot ID " . $request->id;
    }

    public function logs() {
        return BotLog::orderBy('updated_at', 'DESC')->paginate(15);
    }
}
