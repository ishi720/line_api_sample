<?php
// LINE Messaging APIのチャネルシークレット
$channelSecret = "{チャネルシークレット}";

// Line Messaging APIのアクセストークン
$accessToken = "{アクセストークン}";

// 送信先のユーザーまたはグループのID
$userId = "グループのID";

// LINEのMessaging APIからのPOSTリクエストを受け取るエンドポイント
$webhookUrl = "https://example.com/your-webhook-endpoint.php";

// リクエストの署名を確認
$content = file_get_contents("php://input");
$signature = $_SERVER["HTTP_X_LINE_SIGNATURE"];

$hash = hash_hmac("sha256", $content, $channelSecret, true);
$signatureHash = base64_encode($hash);

if ($signature !== $signatureHash) {
    http_response_code(400);
    die("Invalid signature");
}

// メッセージの処理
$events = json_decode($content, true)["events"];

foreach ($events as $event) {
    $eventType = $event["type"];
    $message = $event["message"]["text"];

    //TOOD: メッセージに対するを処理


    // メッセージの返信
    replyToUser($event["replyToken"], $message);
}

function replyToUser($replyToken,$message) {
    global $accessToken, $userId;

    // 送信するメッセージ
    $message = [
        "type" => "text",
        "text" => $message
    ];

    // メッセージオブジェクトを作成
    $messageData = [
        "to" => $userId,
        "messages" => [$message]
    ];


    // Line Messaging APIにPOSTリクエストを送信するためのURL
    $apiUrl = "https://api.line.me/v2/bot/message/push";

    // POSTリクエストをセットアップ
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $accessToken
    ]);

    // POSTリクエストを実行
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo "Error: " . curl_error($ch);
    } else {
        echo "Message sent successfully!";
    }
    curl_close($ch);
}
