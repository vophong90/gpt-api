<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

$prompt = $data["prompt"] ?? "";

if (!$prompt) {
  echo json_encode(["error" => "Thiếu prompt"]);
  exit;
}

$apiKey = "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";
$payload = [
  "model" => "gpt-4o",
  "messages" => [["role" => "user", "content" => $prompt]],
  "temperature" => 0.3
];

$ch = curl_init("https://api.openai.com/v1/chat/completions");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "Content-Type: application/json",
  "Authorization: Bearer $apiKey"
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
