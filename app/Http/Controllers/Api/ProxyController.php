<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemProxies;
use Illuminate\Http\Request;

class ProxyController extends Controller
{
    private $proxyKey = "6fbGkqVxpKM2L8FvRZc1zy4d3mjCwJBD";

    // TODO Proxy hết hạn thì chỉ gia hạn số ngày = hạn sử dụng của bot đang dùng proxy đấy thôi, không fix cứng tránh phí
    public function maintainProxies()
    {
        echo "========================<br>";
        echo "Cập nhật tất cả proxy đang có";
        echo "<br>========================<br><br>";

        // Cập nhật tất cả proxy đang có
        $getProxies = $this->getProxies();
        foreach ($getProxies->list as $getProxy) {
            if (isset($getProxy->host)) {
                \App\Models\SystemProxies::updateOrCreate(
                    [
                        'proxy' => $getProxy->host . ':' . $getProxy->port
                    ],
                    [
                        'expired' => $getProxy->unixtime_end
                    ]);
                echo 'Update thành công ' . $getProxy->host . '<br>';
            }
        }

        echo "<br>========================<br>";
        echo "Kiểm tra những proxy nào hết hạn mà đang dùng thì gia hạn";
        echo "<br>========================<br><br>";

        // Kiểm tra những proxy nào hết hạn mà đang dùng thì gia hạn
        $proxiesExpiredInDB = SystemProxies::where('bot_id', '!=', 0)->where('expired', '<', time())->get();
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

        echo "<br>========================<br>";
        echo "Kiểm tra trong kho nếu dưới 20 proxy thì mua proxy mới";
        echo "<br>========================<br>";

        // Kiểm tra trong kho nếu dưới 10 proxy thì mua proxy mới
        $proxiesInDB = SystemProxies::where('bot_id', 0)->where('is_live', true)->get();
        $proxiesCountToMaintain = 15;
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
}
