<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TokenController extends Controller
{
    public function maintainSystemTokens()
    {
        // Lấy proxy để request
        $proxy = getTinsoftProxy();
        if ($proxy == false) {
            sendMessageTelegram("WARNING: Hàm lấy proxy Tinsoft ko trả về đc proxy nên bot phải chờ tới lần chạy sau");
            Log::error('WARNING: Hàm lấy proxy Tinsoft ko trả về đc proxy nên bot phải chờ tới lần chạy sau');
            return 0;
        }

        // Lấy toàn bộ token đang die, trong 3 tiếng gần đây không được update ra
        $listDieTokens = SystemToken::where('is_live', false)->where('updated_at', '<=', Carbon::now()->subHours(3)->toDateTimeString())->get();
        foreach ($listDieTokens as $dieToken) {
            // Lấy thông tin token
            $tokenInfo = FacebookGet('me', array(), $dieToken->token, $proxy);

            // Nếu token sống lại thì update vào DB, báo về cho vui cửa vui nhà :))
            if (isset($tokenInfo) && isset($tokenInfo->id)) {
                $dieToken->update([
                    'is_live' => true
                ]);

                $countLiveToken = SystemToken::where('is_live', true)->count();
                sendMessageTelegram("GOOD NEWS: Một token bị dẹo (ID " . $dieToken->id . ") vừa bật dậy sống lại, hệ thống hiện có " . $countLiveToken . " token");
                Log::error("GOOD NEWS: Một token bị dẹo vừa bật dậy sống lại, hệ thống hiện có " . $countLiveToken . " token");
                return $dieToken;
            } else {
                // Nếu token chết 24 tiếng rồi mà không gọi dậy được thì xóa đi cho rảnh nợ
                if ($dieToken->updated_at < Carbon::now()->subHours(24)->toDateTimeString()) {
                    $dieToken->delete();

                    $countLiveToken = SystemToken::where('is_live', true)->count();
                    sendMessageTelegram("BAD NEWS: Token ID " . $dieToken->id . " đã die trên 24 giờ mà lay mãi không dậy, quyết định khai trừ khỏi quân ngũ, hệ thống hiện còn " . $countLiveToken . " token");
                    Log::error("BAD NEWS: Token ID " . $dieToken->id . " đã die trên 24 giờ mà lay mãi không dậy, quyết định khai trừ khỏi quân ngũ, hệ thống hiện còn " . $countLiveToken . " token");
                    return 0;
                }
            }
        }

        return "Không cứu, cũng không xóa chiến hữu nào";
    }
}
