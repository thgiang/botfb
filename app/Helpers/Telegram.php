<?php

function sendMessageTelegram($message)
{
    $date = date('H:i:s d/m/Y');
    $message = "Bẩm báo đại vương, \n=== " . $date . " ===\n\n" . $message;
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.telegram.org/bot863613427:AAFR17KC_NZOURdQnbdP2rMxllppalYQL5I/sendMessage?chat_id=-429495942",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => array('text' => '' . $message . ''),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    return $response;
}
