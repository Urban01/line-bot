<?php

$accessToken = 'hFo72VrwKE3AulyxOyNndpbWlrCLr5wm8bHBXrijp0R3O3jXwwwAVVuFXCU37mK6W5u1aV3pYaUzUlQCIh97FnIrZBPqpaB7L/4x7+uXdWtuf1yJHo/hw5q5oOIKMV8oevKVFyKk9GjWvEijL/P7GwdB04t89/1O/w1cDnyilFU=';

$jsonString = file_get_contents('php://input');
//error_log($jsonString);
$jsonObj = json_decode($jsonString);

$message = $jsonObj->{"events"}[0]->{"message"};
$text = $jsonObj->{"events"}[0]->{"message"}->{"text"};
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};
$userId = $jsonObj->{"events"}[0]->{"source"}->{"userId"};

$account_key = 'cf96e207349f429383d69d8f092eab8d'

$keyword = urlencode("'" . $text . "'");
$credencial = 'Authorization: Basic ' . base64_encode($account_key . ":" . $account_key);
$context = stream_context_create(array(
	'http' => array(
	'header' => $credencial
	)
));

$contents = file_get_contents('https://api.datamarket.azure.com/Bing/Search/Image?$format=json&$top=10&Adult='."'".'Strict'."'".'&Query='.$keyword, 0, $context);
$json = json_decode($contents);

$images = array();
foreach ($json->d->results as $result) {
	$images[] = array($result->MediaUrl, $result->Thumbnail->MediaUrl);
}
$rand = rand(0, count($images)-1);
$image_url = $images[$rand][0];
$image_thumb_url = $images[$rand][1];


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
