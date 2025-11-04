<?php
// ===== CORS =====
$origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
header("Access-Control-Allow-Origin: $origin");
header("Vary: Origin");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400");
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

// ===== Input =====
$raw    = file_get_contents("php://input") ?: '';
$input  = json_decode($raw, true) ?: [];
$action = $input['action'] ?? 'moderate';           // 'moderate' | 'chat'
$prompt = $input['prompt'] ?? $input['input'] ?? '';
$modelOverride = $input['model'] ?? null;

// ===== API Key =====
$apiKey = getenv("OPENAI_API_KEY") ?: ($_ENV['OPENAI_API_KEY'] ?? '');
if (!$apiKey) {
  http_response_code(500);
  echo json_encode(["error" => "Missing OPENAI_API_KEY"]);
  exit;
}

// ===== Helper call =====
function call_openai(string $path, array $payload, string $apiKey): void {
  $ch = curl_init("https://api.openai.com/v1/$path");
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
      "Content-Type: application/json",
      "Authorization: Bearer $apiKey",
      "Accept: application/json",
    ],
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
  ]);

  $resp   = curl_exec($ch);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

  if ($resp === false) {
    $err = curl_error($ch);
    curl_close($ch);
    http_response_code(502);
    echo json_encode(["error" => "Upstream error", "detail" => $err]);
    exit;
  }
  curl_close($ch);

  http_response_code($status ?: 200);
  header("X-OpenAI-Status: $status");
  echo $resp;
  exit;
}

// ===== Routes =====
if ($action === 'chat') {
  // Responses API (khuyến nghị cho model mới, ví dụ: gpt-5)
  $model = $modelOverride ?: (getenv("OPENAI_MODEL") ?: "gpt-5"); // xem docs model :contentReference[oaicite:2]{index=2}
  $payload = [
    "model" => $model,
    "input" => [
      ["role" => "system", "content" => "You are a concise assistant."],
      ["role" => "user",   "content" => $prompt],
    ]
  ];
  call_openai('responses', $payload, $apiKey);
} else {
  // Moderation API (khuyến nghị dùng omni-moderation-latest)
  $modModel = $modelOverride ?: "omni-moderation-latest";
  $payload  = [
    "model" => $modModel,
    "input" => $prompt
  ];
  call_openai('moderations', $payload, $apiKey);
}
