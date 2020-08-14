<?php
function getTinsoftProxy()
{
    $proxy = false;
    $needNewProxy = false;

    $tinsoftKey = 'TL1R0qfoVL8MqWnRk82wiXed2aa13DyRSmtNTE';

    // Kiểm tra key Tinsoft này đang cầm proxy nào không
    $getNowProxy = @file_get_contents("http://proxy.tinsoftsv.com/api/getProxy.php?key=" . $tinsoftKey);
    if ($getNowProxy) {
        $getNowProxy = json_decode($getNowProxy);
        if ($getNowProxy->success == false || $getNowProxy->timeout < 60 || $getNowProxy->next_change == 0) {
            $needNewProxy = true;
        } else {
            $proxy = $getNowProxy->proxy;
        }
    }

    // Nếu không thì lấy proxy mới
    if ($needNewProxy == true) {
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

    return $proxy;
}
