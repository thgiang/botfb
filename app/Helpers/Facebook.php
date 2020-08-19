<?php

function getFbDtsg($cookie, $proxy = null)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://m.facebook.com/",
        CURLOPT_PROXY => $proxy,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "authority: m.facebook.com",
            "cache-control: max-age=0",
            "upgrade-insecure-requests: 1",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/81.0.4044.138 Safari/537.36",
            "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "sec-fetch-site: same-origin",
            "sec-fetch-mode: navigate",
            "sec-fetch-user: ?1",
            "sec-fetch-dest: document",
            "accept-language: en-US,en;q=0.9",
            "cookie: " . $cookie
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    if (preg_match("/name=\"fb_dtsg\" value=\"(.*?)\" autocomplete=\"off\"/", $response, $dtsg)) {
        return $dtsg[1];
    } else {
        return false;
    }
}

function reactionPostByCookie($cookie, $dtsg, $postId, $reactionType, $proxy = null)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://m.facebook.com/ufi/reaction/?story_render_location=timeline&feedback_source=0&is_sponsored=0",
        CURLOPT_PROXY => $proxy,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "reaction_type=" . $reactionType . "&ft_ent_identifier=" . $postId . "&m_sess=&fb_dtsg=" . $dtsg,
        CURLOPT_HTTPHEADER => array(
            "authority: m.facebook.com",
            "x-requested-with: XMLHttpRequest",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36",
            "x-response-format: JSONStream",
            "content-type: application/x-www-form-urlencoded",
            "accept: */*",
            "origin: https://m.facebook.com",
            "sec-fetch-site: same-origin",
            "sec-fetch-mode: cors",
            "sec-fetch-dest: empty",
            "referer: https://m.facebook.com",
            "accept-language: vi,vi-VN;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5",
            "cookie: " . $cookie
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $responseData = str_replace("for (;;);", "", $response);
    if (isset(json_decode($responseData)->payload)) {
        return true;
    } else {
        return false;
    }
}

function commentPostByCookie($cookie, $dtsg, $postID, $commentContent, $stickerID = null, $photoId = null, $proxy = null)
{
    $commentContent = "comment_text=" . $commentContent;
    if ($stickerID != null) {
        $commentContent = $commentContent . "&sticker_id=" . $stickerID;
    }
    if ($photoId != null) {
        $commentContent = $commentContent . "&photo_ids[" . $photoId . "]=" . $photoId;
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://m.facebook.com/a/comment.php?fs=8&fr=%252Fprofile.php&actionsource=2&comment_logging&ft_ent_identifier=" . $postID,
        CURLOPT_PROXY => $proxy,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $commentContent . "&privacy_value=0&conversation_guide_session_id=&conversation_guide_shown=none&waterfall_source=photo_comment&submit=%u0110%u0103ng&m_sess=&fb_dtsg=" . $dtsg . "&__a=AYnHMZ8--2zFj8rJX4zb3i53j5KqZc-MNwhEDDC9EIZtsvVv1XU-sdTG62raS8gRAhtSHjZPv7CUwAiA1tCXznN4qh_vpKKOuXgAEEloT9mMBQ",
        CURLOPT_HTTPHEADER => array(
            "authority: m.facebook.com",
            "x-requested-with: XMLHttpRequest",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36",
            "x-response-format: JSONStream",
            "content-type: application/x-www-form-urlencoded",
            "accept: */*",
            "origin: https://m.facebook.com",
            "sec-fetch-site: same-origin",
            "sec-fetch-mode: cors",
            "sec-fetch-dest: empty",
            "referer: https://m.facebook.com",
            "accept-language: vi,vi-VN;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5",
            "cookie: " . $cookie
        ),
    ));

    $response = curl_exec($curl);
    $responseData = str_replace("for (;;);", "", $response);
    if (isset(json_decode($responseData)->payload)) {
        $data = json_decode($responseData);

//        file_put_contents('fb.txt', $cookie."\n", FILE_APPEND);
//        file_put_contents('fb.txt', $dtsg."\n", FILE_APPEND);
//        file_put_contents('fb.txt', $cookie."\n", FILE_APPEND);
//        file_put_contents('fb.txt', $postID."\n", FILE_APPEND);
//        file_put_contents('fb.txt', $commentContent."\n", FILE_APPEND);
//        file_put_contents('fb.txt', $response."\n\n\n", FILE_APPEND);
        if (isset($data->payload->actions[1]->html)) {
            $html = $data->payload->actions[1]->html;
            $re = '/data-commentid="(.*)" data-sigil="comment-body"/s';
            $re2 = '/commentID\":\"(.*)\"}/';
            preg_match($re, $html, $matches);
            preg_match($re2, $html, $matches2);
            if (count($matches) > 1) {
                return $matches[1];
            }
            if (count($matches2) > 1) {
                return $matches2[1];
            }
        }

        return false;
    } else {
        return false;
    }
}

function getPostsFromNewFeed2($cookie, $proxy = null, $postOwnerType = 'all', $ignoreFbIds = array(), $ignoreFbPostIds = array(), $urlToCrawl = "https://mbasic.facebook.com/stories.php", $tryCount = 0)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $urlToCrawl,
        CURLOPT_PROXY => $proxy,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "authority: m.facebook.com",
            "cache-control: max-age=0",
            "upgrade-insecure-requests: 1",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36",
            "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "sec-fetch-site: same-origin",
            "sec-fetch-mode: navigate",
            "sec-fetch-user: ?1",
            "sec-fetch-dest: document",
            "accept-language: vi,vi-VN;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5",
            "cookie: " . $cookie
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    // echo $response;
    $posts = [];

    if ($postOwnerType == 'group') {
        preg_match_all("/groups\/([0-9]+)\?view=permalink&amp;id=([0-9]+)&amp;/", $response, $matches);

        if (isset($matches[2])) {
            $listPostIDs = array_values(array_unique($matches[2]));
            $listGroupIDs = array_values(array_unique($matches[1]));
            foreach ($listPostIDs as $key => $postID) {
                preg_match("/%3Atop_level_post_id.$postID%3Acontent_owner_id_new.([0-9]+)%3A/", $response, $postOwnerID);
                if (isset($postOwnerID[1]) && isset($listGroupIDs[$key])) {
                    $postOwnerID = $postOwnerID[1];
                    $groupID = $listGroupIDs[$key];
                    array_push($posts, (object)[
                        "group_id" => $groupID,
                        "post_id" => $postID,
                        "owner_id" => $postOwnerID
                    ]);
                }
            }
        }
    } elseif ($postOwnerType == 'friend' || $postOwnerType == 'fanpage') {
        preg_match_all("/story\.php\?story_fbid=([0-9]+)&amp;id=([0-9]+)&amp/", $response, $matches);
        if (isset($matches[2])) {
            $listPostOwnerIDs = array_values(array_unique($matches[2]));
            $listPostIDs = array_values(array_unique($matches[1]));

            foreach ($listPostOwnerIDs as $index => $postOwnerID) {
                $postIDOfThisOwner = $listPostIDs[$index];

                if (preg_match("/%3Apage_id\." . $postOwnerID . "%3A/", $response)) {
                    array_push($posts, (object)[
                        "post_id" => $postIDOfThisOwner,
                        "owner_id" => $postOwnerID
                    ]);
                } else {
                    array_push($posts, (object)[
                        "post_id" => $postIDOfThisOwner,
                        "owner_id" => $postOwnerID
                    ]);
                }
            }
        }
    } else {
        preg_match_all("/%3Atop_level_post_id.([0-9]+)%3Acontent_owner_id_new.([0-9]+)%3A/", $response, $matches);
        $listPostOwnerIDs = array_values(array_unique($matches[2]));
        $listPostIDs = array_values(array_unique($matches[1]));
        foreach ($listPostOwnerIDs as $key => $postOwnerID) {
            if (isset($listPostIDs[$key])) {
                $postID = $listPostIDs[$key];
                array_push($posts, (object)[
                    "post_id" => $postID,
                    "owner_id" => $postOwnerID
                ]);
            }
        }
    }

    // Loc black list
    foreach ($posts as $key => $post) {
        if (in_array($post->post_id, $ignoreFbPostIds)) {
            unset($posts[$key]);
        }
        if (in_array($post->owner_id, $ignoreFbIds)) {
            unset($posts[$key]);
        }
    }

    // // Nếu đến đây chưa tìm được post thì crawl tới page tiếp theo để tìm tiếp
    if (count($posts) == 0 && $tryCount <= 5) {
        preg_match("/stories\.php\?aftercursorr\=(.*?)\"/", $response, $nextCursor);
        if (isset($nextCursor[0])) {
            $nextCursor = "https://mbasic.facebook.com/" . rtrim($nextCursor[0], '"');
            $nextTry = $tryCount + 1;
            getPostsFromNewFeed2($cookie, $proxy, $postOwnerType, $ignoreFbIds, $ignoreFbPostIds, $nextCursor, $nextTry);
        }
    }

    return $posts;
}

function getPostsFromGroup($cookie, $groupID, $proxy = null)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://mbasic.facebook.com/groups/" . $groupID,
        CURLOPT_PROXY => $proxy,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "authority: mbasic.facebook.com",
            "cache-control: max-age=0",
            "upgrade-insecure-requests: 1",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36",
            "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "sec-fetch-site: none",
            "sec-fetch-mode: navigate",
            "sec-fetch-user: ?1",
            "sec-fetch-dest: document",
            "accept-language: vi,vi-VN;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5",
            "cookie: " . $cookie
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $listIDs = [];
    preg_match_all("/groups\/([0-9]+)\?view=permalink&amp;id=([0-9]+)&amp;/", $response, $matches);

    if (isset($matches[2])) {
        $listPostIDs = array_values(array_unique($matches[2]));
        foreach ($listPostIDs as $key => $postID) {
            preg_match("/_objid\.$postID%3Acontent_owner_id_new\.([0-9]+)%/", $response, $postOwnerID);
            if (isset($postOwnerID[1])) {
                $postOwnerID = $postOwnerID[1];
                array_push($listIDs, (object)[
                    "group_id" => $groupID,
                    "post_id" => $postID,
                    "owner_id" => $postOwnerID
                ]);
            }
        }
    }

    return $listIDs;
}


function getUserInfoFromUID($uid, $proxy, $token = "EAABwzLixnjYBANPZCGhCAfydyUe912L1ZCci3qKvrai44gxeVHTb7FNWZB8JZB6knEUfyMVKBhxUQb7YJ2PqHZCjGL62ZAE3kZBdSSfNRDrSlWevG5CgpCRq483yvG5ETKXl7ZB1VwixMrmIxEc1Ctox9OET6l3ZBZBMmzLhZBoAZAFZBPL3a6BVhrkD1")
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://graph.facebook.com/" . $uid . "?access_token=" . $token,
        CURLOPT_PROXY => $proxy,
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


function getPostOwner($cookie, $proxy = null, $postID)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://mbasic.facebook.com/" . $postID,
        CURLOPT_PROXY => $proxy,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "authority: mbasic.facebook.com",
            "cache-control: max-age=0",
            "upgrade-insecure-requests: 1",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36",
            "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "sec-fetch-site: none",
            "sec-fetch-mode: navigate",
            "sec-fetch-user: ?1",
            "sec-fetch-dest: document",
            "accept-language: vi,vi-VN;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5",
            "cookie: " . $cookie
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    preg_match("#__tn__=C-R\">(.*?)</a></strong#", $response, $postOwner);
    if (isset($postOwner[0])) {
        return $postOwner[1];
    }

    preg_match("#title>(.*?) - (.*?) \| Facebook#", $response, $postOwner);
    if (isset($postOwner[0])) {
        return $postOwner[1];
    }

    return "Bạn";
}


function getUserInfoFromCookie($cookie, $proxy = null, $token = "EAABwzLixnjYBANPZCGhCAfydyUe912L1ZCci3qKvrai44gxeVHTb7FNWZB8JZB6knEUfyMVKBhxUQb7YJ2PqHZCjGL62ZAE3kZBdSSfNRDrSlWevG5CgpCRq483yvG5ETKXl7ZB1VwixMrmIxEc1Ctox9OET6l3ZBZBMmzLhZBoAZAFZBPL3a6BVhrkD1")
{
    preg_match("/c_user=([0-9]+);/", $cookie, $userID);
    if (isset($userID[1])) {
        return getUserInfoFromUID($userID[1], $proxy, $token);
    }
    return false;
}


function checkProxy($proxy)
{
    $proxyParts = explode(":", $proxy);
    if (count($proxyParts) != 2) {
        return false;
    }

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://jas.plus/ip",
        CURLOPT_PROXY => $proxy,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
    ));
    $response = curl_exec($curl);

    if ($response != "139.180.156.247") {
        return $response;
    }

    return false;
}

// Example: uploadImageToFacebook("https://www.upsieutoc.com/images/2020/07/31/592ccac0a949b39f058a297fd1faa38e.md.jpg", $cookie, $dtsg)
function uploadImageToFacebook($imageURL, $cookie, $dtsg, $text = null, $proxy = null)
{
    $parts = explode('.', $imageURL);
    $extension = '';
    if ($parts && count($parts) > 0) {
        $extension = $parts[count($parts) - 1];
    }
    if ($extension != 'jpg' && $extension != 'png') {
        return false;
    }

    $curlGetImage = curl_init($imageURL);
    $fileName = public_path() . '/image_generator/downloads/' . rand(0, 10000) . '.' . $extension;
//    $fileName = rand(0, 10000) . '.png';
    $fp = fopen($fileName, 'w+');
    //curl_setopt($curlGetImage, CURLOPT_PROXY, $proxy);
    curl_setopt($curlGetImage, CURLOPT_FILE, $fp);
    curl_setopt($curlGetImage, CURLOPT_HEADER, 0);
    curl_exec($curlGetImage);
    curl_close($curlGetImage);
    fclose($fp);

    // Nếu cần ghi text thì ghi lên và ghi đè biến $fileName, bên dưới sẽ gọi hàm unlink để xóa ảnh đi
    if ($text != null) {
        $fileName = writeTextToImage($fileName, $text);
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://upload.facebook.com/_mupload_/photo/x/saveunpublished/?allow_spherical_photo=true&thumbnail_width=80&thumbnail_height=80&waterfall_id=bec27f53989369fe074a97aead0d03eb&waterfall_app_name=web_m_touch&waterfall_source=photo_comment&fb_dtsg=" . $dtsg . "&jazoest=21951&m_sess=&__csr=&__req=h&__a=" . $dtsg,
        CURLOPT_PROXY => $proxy,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => array('photo' => new CURLFile(getcwd() . "/" . $fileName)),
        CURLOPT_HTTPHEADER => array(
            "authority: upload.facebook.com",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36",
            "content-type: multipart/form-data",
            "accept: */*",
            "origin: https://m.facebook.com",
            "sec-fetch-site: same-site",
            "sec-fetch-mode: cors",
            "sec-fetch-dest: empty",
            "referer: https://m.facebook.com/",
            "accept-language: vi,vi-VN;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5",
            "cookie: " . $cookie
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $responseData = str_replace("for (;;);", "", $response);
    $json = json_decode($responseData);
    unlink($fileName);
    if (isset($json->payload) && isset($json->payload->fbid)) {
        return $json->payload->fbid;
    } else {
        return false;
    }
}

function checkCookieJoinedGroup($cookie, $groupID, $proxy = null)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://mbasic.facebook.com/groups/" . $groupID,
        CURLOPT_PROXY => $proxy,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "authority: mbasic.facebook.com",
            "cache-control: max-age=0",
            "upgrade-insecure-requests: 1",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36",
            "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "sec-fetch-site: none",
            "sec-fetch-mode: navigate",
            "sec-fetch-user: ?1",
            "sec-fetch-dest: document",
            "accept-language: vi,vi-VN;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5",
            "cookie: " . $cookie
        ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    if (!preg_match("/group\/join\/\?group_id/", $response)) {
        return true;
    } else {
        return false;
    }
}

function randomStickerOfCollection($cookie, $dtsg, $stickerColletionID, $proxy = null)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://m.facebook.com/stickers/" . $stickerColletionID . "/images/",
        CURLOPT_PROXY => $proxy,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "m_sess=&fb_dtsg=" . $dtsg . "&__csr=&__req=z",
        CURLOPT_HTTPHEADER => array(
            "authority: m.facebook.com",
            "x-requested-with: XMLHttpRequest",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36",
            "x-response-format: JSONStream",
            "content-type: application/x-www-form-urlencoded",
            "accept: */*",
            "origin: https://m.facebook.com",
            "sec-fetch-site: same-origin",
            "sec-fetch-mode: cors",
            "sec-fetch-dest: empty",
            "referer: https://m.facebook.com/story.php",
            "accept-language: vi,vi-VN;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5",
            "cookie: " . $cookie
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $responseData = str_replace("for (;;);", "", $response);
    $json = json_decode($responseData);
    if (isset($json->payload) && isset($json->payload->payload)) {
        $payload = $json->payload->payload;
        return $payload[array_rand($payload, 1)]->id;
    }

    return false;
}

function DoShortCode($str, $extraData = array())
{
    $str = str_replace('{enter}', "\n", $str);
    $str = str_replace('{ngay}', date("d", time()), $str);
    $str = str_replace('{thang}', date("m", time()), $str);
    $str = str_replace('{nam}', date("Y", time()), $str);
    $str = str_replace('{gio}', date("H", time()), $str);
    $str = str_replace('{phut}', date("i", time()), $str);
    $str = str_replace('{giay}', date("s", time()), $str);

    // Shortcode tên
    $name = 'Bạn ơi';
    if (!empty($extraData['name'])) {
        $name = $extraData['name'];
    }
    $str = str_replace('{ten}', $name, $str);

    // Shortcode icon
    if (preg_match("/{icon}/", $str)) {
        $parts = explode('{icon}', $str);
        $str = "";
        for ($i = 0; $i < count($parts); $i++) {
            $str .= $parts[$i];
            if ($i < count($parts) - 1) {
                $str .= RandomEmotion();
            }
        }
    }

    return trim($str);
}

function RandomEmotion()
{
    $emotions = array("💦", "😀", "😁", "😂", "🤣", "😃", "😄", "😅", "😆", "😉", "😊", "😋", "😎", "😍", "😘", "😗", "😙", "😚", "☺", "🙂", "🤗", "🤩", "🤔", "🤨", "😐", "😑", "😶", "🙄", "😏", "😣", "😥", "😮", "🤐", "😯", "😪", "😫", "😴", "😌", "😛", "😜", "😝", "🤤", "😒", "😓", "😔", "😕", "🙃", "🤑", "😲", "☹", "🙁", "😖", "😞", "😟", "😤", "😢", "😭", "😦", "😧", "😨", "😩", "🤯", "😬", "😰", "😱", "😳", "🤪", "😵", "😡", "😠", "🤬", "😷", "🤒", "🤕", "🤢", "🤮", "🤧", "😇", "🤠", "🤡", "🤥", "🤫", "🤭", "🧐", "🤓", "😈", "👿", "👹", "👺", "💀", "☠", "👻", "👽", "👾", "🤖", "💩", "😺", "😸", "😹", "😻", "😼", "😽", "🙀", "😿", "😾", "🙈", "🙉", "🙊", "👶", "🧒", "👦", "👧", "🧑", "👨", "👩", "🧓", "👴", "👵", "🤳", "💪", "👈", "👉", "☝", "👆", "🖕", "👇", "✌", "🤞", "🖖", "🤘", "🤙", "🖐", "✋", "👌", "👍", "👎", "✊", "👊", "🤛", "🤜", "🤚", "👋", "🤟", "✍", "👏", "👐", "🙌", "🤲", "🙏", "🤝", "💅", "👂", "👃", "👣", "👀", "👁", "👁", "‍", "🗨", "🧠", "👅", "👄", "💋", "💘", "❤", "💓", "💔", "💕", "💖", "💗", "💙", "💚", "💛", "🧡", "💜", "🖤", "💝", "💞", "💟", "❣", "💌", "💤", "💢", "💣", "💥", "💦", "💨", "💫", "💬", "🗨", "🗯", "💭", "🤴", "👸", "👳", "‍", "♂", "👳", "‍", "♀", "👲", "🧕", "🧔", "👱", "‍", "♂", "👱", "‍", "♀", "🤵", "👰", "🤰", "🤱", "👼", "🙍", "‍", "♂", "🙍", "‍", "♀", "🙎", "‍", "♂", "🙎", "‍", "♀", "🙅", "‍", "♂", "🙅", "‍", "♀", "🙆", "‍", "♂", "🙆", "‍", "♀", "💁", "‍", "♂", "💁", "‍", "♀", "🙋", "‍", "♂", "🙋", "‍", "♀", "🙇", "‍", "♂", "🙇", "‍", "♀", "🤦", "‍", "♂", "🤦", "‍", "♀", "🤷", "‍", "♂", "🤷", "‍", "♀", "💆", "‍", "♂", "💆", "‍", "♀", "💇", "‍", "♂", "💇", "‍", "♀", "🚶", "‍", "♂", "🚶", "‍", "♀", "🏃", "‍", "♂", "🏃", "‍", "♀", "💃", "🕺", "🛀", "🛌", "🕴", "🗣", "👤", "👥", "👫", "👬", "👭", "👩", "‍", "❤", "‍", "💋", "‍", "👨", "👨", "‍", "❤", "‍", "💋", "‍", "👨", "👩", "‍", "❤", "‍", "💋", "‍", "👩", "👩", "‍", "❤", "‍", "👨", "👨", "‍", "❤", "‍", "👨", "👩", "‍", "❤", "‍", "👩");
    return $emotions[rand(0, count($emotions) - 1)];
}

function RandomComment()
{
    $emotion = RandomEmotion();
    $comments = array("Anh có xô hay chậu gì không? Hứng hộ tình cảm của em dành cho anh đi ",
        "Anh vô gia cư hay sao cứ ở trong đầu em mãi...",
        "Anh có thích Sơn Tùng không? Em không phải Sơn Tùng nhưng em vẫn âm thầm bên anh",
        "Chưa quen đừng bảo em kiêu. Quen rồi mới thấy đáng yêu cực kỳ!",
        "Nghe nói con gái như em rất là khó gần?\nAnh hỏi cho vui chứ không có cần :'>",
        "Không có gì là mãi mãi. Chỉ có từ \"Mãi mãi\" mới là mãi mãi.",
        "Có 1 sự thật là… bạn sẽ trẻ mãi… cho tới tận lúc già.",
        "Bí quyết để sống lâum là đừng bao giờ ngừng thở.",
        "Trứng rán cần mỡ, bắp cần bơ, yêu không cần cớ, cần cậu cơ!",
        "Hôm nay anh học toán hình. Tròn, vuông chẳng có; toàn hình bóng em ♥",
        "Đôi môi này chỉ ăn cơm với cá. Đã bao giờ biết thơm má ai đâu :*",
        "Nghiện ngập còn có thể cai. Yêu em chỉ đầu thai mới hết :\">",
        "Hôm qua là monday, hôm nay là tuesday. Vậy hôm nào là bên em đây?",
        "Tính em không thích được khen, nhưng em lại thích Nô-en có quà.",
        "Người ta vá áo bằng kim, anh cho em hỏi vá tim bằng gì?",
        "Vì nàng nói nàng thích màu xanh\nTôi đem lòng tôi yêu cả bầu trời.",
        "Trong ngàn vạn cách để hạnh phúc, trực tiếp nhất chính là ngắm nhìn em.",
        "Trộm cắp bây giờ nhanh thật, quay đi quay lại mất luôn trái tim.",
        "Nhân chi sơ, tính bản thiện.\nThích cậu đến nghiện, thì phải làm sao?",
        "Hỏi em đi đứng thế nào. Năm lần, bảy lượt ngã vào tim anh?",
        "Em viết hộ anh một phương trình, kết quả chỉ có chúng mình được không?",
        "Chẳng cần bánh ngọt với kem. Chỉ cần em nói yêu anh, đủ rồi!",
        "Bệnh phổi là do thuốc, bệnh gan là do nhậu\nBệnh tim chắc chắn là do cậu rồi!",
        "Em ơi nắng ấm xa rồi. Đông sang, gió lạnh anh cần em thôi!",
        "Anh không thích nhạc Only C. Em chỉ thích only em.",
        "Nước trong nước chảy quanh chùa.\nAnh xin em đấy bỏ bùa anh đi.",
        "Đường khuya thì vắng, nhà anh thì xa.\nNhiều nguy hiểm lắm, ngủ nhà em nha!",
        "Đầu tiên hãy nói nhớ anh đi, sau đó hỏi anh đang làm gì?\nCùng vài câu quan tâm sâu sắc. Đơn giản như thế, em làm đi!",
        "Anh không muốn làm người xấu, cũng không muốn làm người tốt.\nAnh chỉ muốn làm người yêu em.",
        "Muốn mời em một chén trà. Nhưng sợ thành người một nhà với em.",
        "Noel anh vẫn một mình. Nếu em cũng thế thì mình yêu thôi.",
        "Soái ca là của ngôn tình, còn anh là của một mình em thôi.",
        "Ở hiền thì gặp lành. Vậy ở đâu thì gặp anh?",
        "Đen Vâu thì muốn trồng rau nuôi cá.\nCòn anh thì đang hỏi má để nuôi thêm em.",
        "Xuân kiếm lì xì, Hạ kiếm kem. Thu kiếm hoa sữa, Đông kiếm em.",
        "Em như búp bê trên cành, biết ăn biết ngủ, biết kiếm tiền và yêu anh.",
        "Anh có thể ship cho em một ly nâu đá. Cùng một vài cái hôn má được không?",
        "Nếu em cảm thấy không phiền. Mùa đông đang tới, yêu liền được không?",
        "Em sinh ra không phải để vất vả. Mà để sau này được gả cho anh.",
        "Trời lạnh ra đường cậu nhớ mang theo tớ nhé!",
        "Em thích chiều hoàng hôn buông. Anh thích chiều buồn hôn em.",
        "Con cò mà đi ăn đêm, đậu phải cành mềm lộn cổ xuống ao.\nAnh đây không uống ngụm nào, vẫn say ngây ngất ngã vào tình em!",
        "Đến giầy dép còn có đôi, cớ sao em lại đơn côi thế này?",
        "Em là cô gái mang giày trắng. Ngược đời ngược nắng đi tìm anh.",
        "Cuộc sống thì giống cuộc đời, còn em thì giống bạn đời của anh.",
        "Ta mua viên thuốc ngừng thương. Người nhầm bán thuốc đơn phương cả đời.",
        "Thằng bờm thì thích nắm xôi, còn em thích nắm tay tôi chứ gì?",
        "Tim em đã bật đèn xanh. Cớ sao anh mãi đạp phanh thế này?"
    );
    return $comments[rand(0, count($comments) - 1)] . " " . $emotion;
}

function FacebookGet($path, $queries, $access_token, $proxy = null)
{
    if (empty($proxy)) {
        $proxy = null;
    }
    $queryString = "";
    foreach ($queries as $key => $value) {
        $queryString .= "&" . $key . "=" . $value;
    }
    $urlToRequest = 'https://graph.facebook.com/v2.4/' . $path . '?access_token=' . $access_token . $queryString;
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $urlToRequest,
        CURLOPT_PROXY => $proxy,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
    ));

    $dataString = curl_exec($curl);

    curl_close($curl);

    if (!$dataString) {
        return json_encode((object)array());
    } else {
        return json_decode($dataString);
    }
}

function getBasicInfoFromCookie($cookie, $proxy = null)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://mbasic.facebook.com/me",
        CURLOPT_PROXY => $proxy,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => array(
            "authority: mbasic.facebook.com",
            "upgrade-insecure-requests: 1",
            "user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36",
            "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9",
            "sec-fetch-site: none",
            "sec-fetch-mode: navigate",
            "sec-fetch-user: ?1",
            "sec-fetch-dest: document",
            "accept-language: vi,vi-VN;q=0.9,fr-FR;q=0.8,fr;q=0.7,en-US;q=0.6,en;q=0.5",
            "cookie: " . $cookie
        ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    preg_match("/\<title\>(.*)<\/title\>/", $response, $name);
    if (isset($name[1]) && !preg_match("/facebook/", strtolower($name[1]))) {
        return $name[1];
    }
    return false;
}
