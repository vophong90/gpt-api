<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  http_response_code(200);
  exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$prompt = $input["prompt"] ?? "";
$apiKey = getenv("OPENAI_API_KEY");

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
  "model" => "gpt-4-1106-preview",
  "messages" => [
    ["role" => "user", "content" => $prompt]
  ],
  "temperature" => 0.2
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "Authorization: Bearer $apiKey"
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
