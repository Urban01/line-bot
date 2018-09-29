<?php

$accessToken = 'hFo72VrwKE3AulyxOyNndpbWlrCLr5wm8bHBXrijp0R3O3jXwwwAVVuFXCU37mK6W5u1aV3pYaUzUlQCIh97FnIrZBPqpaB7L/4x7+uXdWtuf1yJHo/hw5q5oOIKMV8oevKVFyKk9GjWvEijL/P7GwdB04t89/1O/w1cDnyilFU=';

$jsonString = file_get_contents('php://input');
//error_log($jsonString);
$jsonObj = json_decode($jsonString);

$message = $jsonObj->{"events"}[0]->{"message"};
$text = $jsonObj->{"events"}[0]->{"message"}->{"text"};
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};
$userId = $jsonObj->{"events"}[0]->{"source"}->{"userId"};

$headers = "Ocp-Apim-Subscription-Key: cf96e207349f429383d69d8f092eab8d\r\n";
$options = array ( 'http' => array (
                        'header' => $headers,
                        'method' => 'GET' ));
                        
$context = stream_context_create($options);
$result = file_get_contents('https://api.cognitive.microsoft.com/bing/v7.0/images/search' . "?q=" . urlencode($text), false, $context);

$headers = array();
foreach ($http_response_header as $k => $v) {
	$h = explode(":", $v, 2);
    if (isset($h[1]))
    	if (preg_match("/^BingAPIs-/", $h[0]) || preg_match("/^X-MSEdge-/", $h[0]))
        	$headers[trim($h[0])] = trim($h[1]);
}

list($headers, $json) = array($headers, $result);

$json = json_decode($json, true);

$image_url = $json["value"][0]["contentUrl"];
$image_thumb_url = $json["value"][0]["thumbnailUrl"];;

$response_format = [
	'type' => 'image',
	'originalContentUrl' => $image_url,
	'previewImageUrl' => $image_thumb_url
];

$post_data = [
	"replyToken" => $replyToken,
	"messages" => [$response_format]
];


$ch = curl_init('https://api.line.me/v2/bot/message/reply');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json; charset=UTF-8',
    'Authorization: Bearer ' . $accessToken
));
$result = curl_exec($ch);
error_log($result);
curl_close($ch);
