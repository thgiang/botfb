<?php
function getTinsoftProxy()
{
    $proxy = false;
    $needNewProxy = false;
    $lastRequest = file_get_contents("last_request_tinsoft.txt");
    $allowRequest = false;
    if (time() - (int)$lastRequest > 10) {
        $allowRequest = true;
    }

    $tinsoftKey = 'TL1R0qfoVL8MqWnRk82wiXed2aa13DyRSmtNTE';

    // Kiểm tra key Tinsoft này đang cầm proxy nào không
    $getNowProxy = @file_get_contents("http://proxy.tinsoftsv.com/api/getProxy.php?key=" . $tinsoftKey);
    if ($getNowProxy) {
        $getNowProxy = json_decode($getNowProxy);
        if ($getNowProxy->success == false || $getNowProxy->next_change == 0) {
            $needNewProxy = true;
        } else {
            $proxy = $getNowProxy->proxy;
        }
    }

    // Nếu không thì lấy proxy mới
    if ($needNewProxy == true && $allowRequest == true) {
        $getNewProxy = @file_get_contents("http://proxy.tinsoftsv.com/api/changeProxy.php?key=" . $tinsoftKey);
        if ($getNewProxy) {
            $getNewProxy = json_decode($getNewProxy);
            if ($getNewProxy->success == true) {
                $proxy = $getNewProxy->proxy;
            }
        }
    }

    if ($proxy == false) {
        sendMessageTelegram("Lấy proxy từ Tinsoft thất bại");
    }

    fwrite(fopen("last_request_tinsoft.txt", "w+"), time());
    return $proxy;
}
