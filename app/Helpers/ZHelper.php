<?php

namespace App\Helpers;
use Carbon\Carbon;

class ZHelper
{
    public static function RandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

	public static function NearestTime($timeNow, array $validHours) {
		$nowH = date("H", $timeNow);
		if (in_array($nowH, $validHours)) {
			//return $timeNow;
			// Cho nó luôn về giây thứ 0 của phút bằng cách trừ số giây lẻ
			return (int)$timeNow - (int)date('s', $timeNow);
		}

		$returnTime = time();
		$foundTime = false;
		foreach ($validHours as $hour) {
			if ($hour > $nowH) {
				$time = Carbon::now();
				$returnTime = Carbon::create($time->year, $time->month, $time->day, $hour, str_pad(rand(0, 5), 2, "0", STR_PAD_LEFT), str_pad(rand(0, 30), 2, "0", STR_PAD_LEFT))->timestamp;
				$foundTime = true;
				break;
			}
		}

		// Nếu tới đây vẫn ko tìm đc giờ phù hợp thì để ngày mai chạy ngay múi giờ đầu tiên
		if (!$foundTime) {
			$time = Carbon::tomorrow();
			$returnTime = Carbon::create($time->year, $time->month, $time->day, $validHours[0], str_pad(rand(0, 5), 2, "0", STR_PAD_LEFT), str_pad(rand(0, 30), 2, "0", STR_PAD_LEFT))->timestamp;
		}

		return $returnTime;
	}
}
