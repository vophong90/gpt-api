<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);
$prompt = $input["prompt"] ?? "";
$apiKey = getenv("OPENAI_API_KEY");

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  "model" => "gpt-4o",
  "messages" => [
    ["role" => "user", "content" => $prompt]
  ],
  "temperature" => 0.2
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "Authorization: Bearer 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"
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
