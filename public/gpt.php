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
$overrideModel = $input["model"] ?? null;

$apiKey = getenv("OPENAI_API_KEY");
$model  = $overrideModel ?: (getenv("OPENAI_MODEL") ?: "gpt-5"); // <-- đổi mặc định sang GPT-5

if (!$apiKey) {
  http_response_code(500);
  echo json_encode(["error" => "Missing OPENAI_API_KEY"]);
  exit;
}

$payload = [
  "model" => $model,
  "messages" => [
    // Bạn có thể thêm system prompt mặc định ở đây nếu muốn chuẩn hoá đầu ra moderation, v.v.
    ["role" => "user", "content" => $prompt]
  ],
  "temperature" => 0.2
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "Authorization: Bearer $apiKey"
]);

$response = curl_exec($ch);
if ($response === false) {
  http_response_code(502);
  echo json_encode(["error" => "Upstream error: ".curl_error($ch)]);
  curl_close($ch);
  exit;
}
curl_close($ch);

echo $response;
