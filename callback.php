<?php

$accessToken = 'hFo72VrwKE3AulyxOyNndpbWlrCLr5wm8bHBXrijp0R3O3jXwwwAVVuFXCU37mK6W5u1aV3pYaUzUlQCIh97FnIrZBPqpaB7L/4x7+uXdWtuf1yJHo/hw5q5oOIKMV8oevKVFyKk9GjWvEijL/P7GwdB04t89/1O/w1cDnyilFU=';

$jsonString = file_get_contents('php://input');
//error_log($jsonString);
$jsonObj = json_decode($jsonString);

$message = $jsonObj->{"events"}[0]->{"message"};
$text = $jsonObj->{"events"}[0]->{"message"}->{"text"};
$replyToken = $jsonObj->{"events"}[0]->{"replyToken"};
$userId = $jsonObj->{"events"}[0]->{"source"}->{"userId"};

$response = chat($text);

$response_format_text = [
	"type" => "text",
	"text" => $response
];

$post_data = [
	"replyToken" => $replyToken,
	"messages" => [$response_format_text]
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


function chat($text) {
    // docomo chatAPI
    $api_key = '4b39684f54415547777235766638784644694377556c356c335a474268362e4c55436c3835384b324d5934';
    $api_url = sprintf('https://api.apigw.smt.docomo.ne.jp/naturalChatting/v1/dialogue?APIKEY=%s', $api_key);
    $req_body = array('utt' => $text);

    $headers = array(
        'Content-Type: application/json; charset=UTF-8',
    );
    $options = array(
        'http'=>array(
            'method'  => 'POST',
            'header'  => implode("\r\n", $headers),
            'content' => json_encode($req_body),
            )
        );
    $stream = stream_context_create($options);
    $res = json_decode(file_get_contents($api_url, false, $stream));

    return $res->utt;
}