<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bot;
use App\Models\SystemProxy;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ProxyController extends Controller
{
    private $proxyKey = "6fbGkqVxpKM2L8FvRZc1zy4d3mjCwJBD";

    // TODO Proxy hết hạn thì chỉ gia hạn số ngày = hạn sử dụng của bot đang dùng proxy đấy thôi, không fix cứng tránh phí
    public function maintainProxies()
    {
        // Những acc nào đang active mà không có proxy + không update trong 5 phút trở lại đây thì lấy trong kho ra phát (5 phút là thời gian cố hồi sinh proxy để thế chỗ)
        $accountsNotHaveProxy = Bot::where('is_active', true)->where('proxy', null)->where('updated_at', '<=', Carbon::now()->subMinutes(5)->toDateTimeString())->get();
        foreach ($accountsNotHaveProxy as $accountNotHaveProxy) {
            $getProxy = SystemProxy::where('bot_id', 0)->where('is_live', true)->first();
            if ($getProxy) {
                $accountNotHaveProxy->proxy = $getProxy->proxy;
                $accountNotHaveProxy->count_error = 0;
                $accountNotHaveProxy->error_log = 'Đã thay mới proxy cho tài khoản ' . $accountNotHaveProxy->id . ' lúc ' . date("d/m/Y H:i:s") . '';
                $accountNotHaveProxy->save();

                $getProxy->bot_id = $accountNotHaveProxy->id;
                $getProxy->save();

                sendMessageTelegram('Đã thay mới proxy cho tài khoản ' . $accountNotHaveProxy->id . ' lúc ' . date("d/m/Y H:i:s") . '');
            }
        }

        // Có những proxy bị đánh dấu là đang làm việc, nhưng thực tế chả con bot nào dùng. Lỗi từ đâu đấy sẽ tìm dần, nhưng trước mắt phải auto chuyển các proxy này về free đã
        $proxiesAreUsingInProxyTable = SystemProxy::where('bot_id', '!=', 0)->pluck('proxy')->toArray();
        $proxiesAreUsingInBotsTable = Bot::where('proxy', '!=', null)->pluck('proxy')->toArray();
        $proxiesNotFreeButNotWorkInBotsTable = array_diff($proxiesAreUsingInProxyTable, $proxiesAreUsingInBotsTable);
        foreach ($proxiesNotFreeButNotWorkInBotsTable as $proxyNotFreeButNotWorkInBotsTable) {
            SystemProxy::where('proxy', $proxyNotFreeButNotWorkInBotsTable)->update([
                'bot_id' => 0
            ]);
        }
//    echo "Có " . count($proxiesAreUsingInProxyTable) . " proxy đang làm việc trong bảng proxy <br>";
//    echo "Có " . count($proxiesAreUsingInBotsTable) . " proxy đang làm việc trong bảng bots <br>";

        echo "========================<br>";
        echo "Cập nhật tất cả proxy đang có";
        echo "<br>========================<br><br>";


        // Cập nhật tất cả proxy đang có
        $getProxies = $this->getProxies();
        foreach ($getProxies->list as $getProxy) {
            if (isset($getProxy->host)) {
                // Tìm proxy này trong DB
                $getProxyFromDB = SystemProxy::where('proxy', $getProxy->host . ':' . $getProxy->port)->first();
                if ($getProxyFromDB) {
                    // Nếu proxy nào đang báo die thì báo về server để check lại
                    if ($getProxyFromDB->is_live == false) {
                        echo 'Proxy ' . $getProxy->id . ' đang báo die, gọi về server để check lại <br>';
                        $this->reportProxyDie($getProxy->id);
                        $getProxyFromDB->is_live = true;
                    }
                    $getProxyFromDB->expired = $getProxy->unixtime_end;
                    $getProxyFromDB->save();
                    echo 'Update thành công ' . $getProxy->host . '<br>';
                } else {
                    $addNewProxyToDB = new SystemProxy();
                    $addNewProxyToDB->proxy = $getProxy->host . ':' . $getProxy->port;
                    $addNewProxyToDB->is_live = true;
                    $addNewProxyToDB->expired = $getProxy->unixtime_end;
                    $addNewProxyToDB->save();
                }
            }
        }

        echo "<br>========================<br>";
        echo "Kiểm tra những proxy nào hết hạn mà đang dùng thì gia hạn";
        echo "<br>========================<br><br>";

        // Kiểm tra những proxy nào hết hạn mà đang dùng thì gia hạn
        $proxiesExpiredInDB = SystemProxy::where('bot_id', '!=', 0)->where('expired', '<', time())->get();
        if ($proxiesExpiredInDB && $proxiesExpiredInDB->count() > 0) {
            foreach ($proxiesExpiredInDB as $proxyNeedRenew) {
                $proxyToRenew = $proxyNeedRenew->proxy;
                sendMessageTelegram("Proxy " . $proxyToRenew . " cần gia hạn");
                foreach ($getProxies->list as $getProxy) {
                    if (isset($getProxy->host) && (($getProxy->host . ':' . $getProxy->port) == $proxyToRenew)) {
                        $idInProxyAZ = $getProxy->id;
                        $period = 7;
                        $this->renewProxy($idInProxyAZ, $period);
                        sendMessageTelegram("Gia hạn proxy " . $proxyToRenew . " thêm " . $period . " ngày. ID từ bên mua: " . $idInProxyAZ);
                    }
                }
            }
        } else {
            echo "Không có proxy nào hết hạn<br>";
        }

        // Kiểm tra trong kho nếu dưới $proxiesCountToMaintain proxy thì mua proxy mới
        $proxiesInDB = SystemProxy::where('bot_id', 0)->where('is_live', true)->get();
        $proxiesCountToMaintain = 10;

        echo "<br>========================<br>";
        echo "Kiểm tra trong kho nếu dưới " . $proxiesCountToMaintain . " proxy thì mua proxy mới";
        echo "<br>========================<br>";

        if ($proxiesInDB->count() < $proxiesCountToMaintain) {
            $period = 30;
            $proxiesNeedToBuy = $proxiesCountToMaintain - $proxiesInDB->count();
            $this->buyProxies($proxiesNeedToBuy, $period);
            sendMessageTelegram("Kho thiếu " . $proxiesNeedToBuy . " proxy, đã đặt lệnh mua");
            return "Kho thiếu " . $proxiesNeedToBuy . " proxy, đã đặt lệnh mua <br>";
        } else {
            echo "Kho đủ proxy <br>";
        }

        echo "<br>========================<br>";
        return "<br>Xong tác vụ check proxy<br>";
    }

    public function getProxies()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://proxyaz.com/api/" . $this->proxyKey . "/getproxy",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function buyProxies($count, $period)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://proxyaz.com/api/" . $this->proxyKey . "/buy?count=" . $count . "&period=" . $period . "&country=vn",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function renewProxy($ids, $period)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://proxyaz.com/api/" . $this->proxyKey . "/prolong?period=" . $period . "&ids=" . $ids,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function reportProxyDie($proxyID)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://proxyaz.com/api/" . $this->proxyKey . "/check?ids=" . $proxyID,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET"
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    public function replaceProxy($proxyDie, $botID)
    {
        // Chuyển proxy bị die về kho, để trạng thái thành die
        SystemProxy::where('proxy', $proxyDie)->update(['is_live' => false, 'bot_id' => 0]);

        // Lấy 1 proxy mới
        $getNewProxy = SystemProxy::where('bot_id', 0)->where('is_live', true)->first();
        if ($getNewProxy) {
            $getNewProxy->update(['bot_id' => $botID]);
            return $getNewProxy->proxy;
        } else {
            return false;
        }
    }
}
