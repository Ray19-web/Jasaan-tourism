<?php
header('Content-Type: application/json; charset=utf-8');

define('JT_RATE_LIMIT_MAX', 20);
define('JT_RATE_LIMIT_WINDOW', 60);
require_once __DIR__ . '/db.php';

function kuyaRespond(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function kuyaRequestJson(string $url, array $payload, array $headers, int $timeout = 60): array
{
    if (!function_exists('curl_init')) {
        return ['status' => 0, 'data' => null];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_HTTPHEADER => $headers,
    ]);

    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    $decoded = json_decode($response, true);
    return [
        'status' => $status,
        'data' => is_array($decoded) ? $decoded : null,
    ];
}

function kuyaGetGeminiApiKey(): string
{
    $apiKey = trim((string) getenv('GEMINI_API_KEY'));
    if ($apiKey !== '') {
        return $apiKey;
    }

    $localConfigPath = __DIR__ . '/gemini.local.php';
    if (!is_file($localConfigPath)) {
        return '';
    }

    $config = require $localConfigPath;
    return is_array($config) ? trim((string) ($config['api_key'] ?? '')) : '';
}

function kuyaGetAssets(mysqli $conn, string $question): array
{
    $result = $conn->query(
        "SELECT
            a.asset_name,
            a.location,
            a.description,
            s.status_code AS asset_status,
            a.status_note,
            GROUP_CONCAT(DISTINCT t.type_name ORDER BY t.type_name SEPARATOR ', ') AS type_name,
            ati.transportation,
            ati.nearby_stay,
            ati.travel_tips,
            ati.estimated_cost,
            ati.travel_time,
            ati.best_time,
            ati.difficulty
         FROM assets a
         LEFT JOIN asset_type_assignments ata ON ata.asset_id = a.asset_id
         LEFT JOIN asset_types t ON t.type_id = ata.type_id AND t.deleted_at IS NULL
         LEFT JOIN asset_statuses s ON s.status_id = a.status_id
         LEFT JOIN asset_travel_info ati ON ati.asset_id = a.asset_id
         WHERE a.deleted_at IS NULL
         GROUP BY a.asset_id
         ORDER BY a.asset_id DESC
         LIMIT 40"
    );

    if (!$result) {
        return [];
    }

    $tokens = preg_split('/[^a-z0-9]+/i', strtolower($question)) ?: [];
    $tokens = array_values(array_filter($tokens, static fn ($token) => strlen($token) >= 3));
    $assets = [];

    while ($row = $result->fetch_assoc()) {
        $searchable = strtolower(implode(' ', array_filter([
            $row['asset_name'],
            $row['location'],
            $row['type_name'],
            $row['description'],
            $row['travel_tips'],
        ])));
        $score = 0;

        foreach ($tokens as $token) {
            if (str_contains($searchable, $token)) {
                $score++;
            }
        }

        $row['_score'] = $score;
        $assets[] = $row;
    }

    usort($assets, static fn (array $a, array $b) => $b['_score'] <=> $a['_score']);
    $matches = array_values(array_filter($assets, static fn (array $asset) => $asset['_score'] > 0));

    if ($matches === []) {
        return array_slice($assets, 0, 3);
    }

    $topScore = (int) $matches[0]['_score'];
    if ($topScore >= 2) {
        $matches = array_values(array_filter(
            $matches,
            static fn (array $asset) => (int) $asset['_score'] === $topScore
        ));
    }

    return array_slice($matches, 0, 5);
}

function kuyaBuildContext(array $assets): string
{
    if ($assets === []) {
        return 'No published tourism records are available.';
    }

    $records = [];
    foreach ($assets as $asset) {
        $details = [
            'Name: ' . $asset['asset_name'],
            'Type: ' . ($asset['type_name'] ?: 'Unclassified'),
            'Location: ' . ($asset['location'] ?: 'Not listed'),
            'Status: ' . ($asset['asset_status'] ?: 'open'),
            'Estimated cost: ' . ($asset['estimated_cost'] ?: 'Not listed'),
            'Travel time: ' . ($asset['travel_time'] ?: 'Not listed'),
            'Best time: ' . ($asset['best_time'] ?: 'Not listed'),
            'Transportation: ' . ($asset['transportation'] ?: 'Not listed'),
            'Nearby stay: ' . ($asset['nearby_stay'] ?: 'Not listed'),
            'Tips: ' . ($asset['travel_tips'] ?: 'Not listed'),
        ];
        $records[] = implode("\n", $details);
    }

    return implode("\n\n---\n\n", $records);
}

function kuyaGetExchangeRate(): ?array
{
    if (!function_exists('curl_init')) {
        return null;
    }

    $ch = curl_init('https://api.frankfurter.dev/v2/rate/php/usd');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    $data = is_string($response) ? json_decode($response, true) : null;
    return $status === 200 && is_array($data) && !empty($data['rate']) ? $data : null;
}

$input = json_decode((string) file_get_contents('php://input'), true);
$question = trim((string) ($input['message'] ?? ''));

if ($question === '' || mb_strlen($question) > 800) {
    kuyaRespond(422, ['success' => false, 'message' => 'Please send a question of up to 800 characters.']);
}

$currencyRate = null;
if (preg_match('/\b(usd|dollar|exchange|convert|peso|php)\b/i', $question)) {
    $currencyRate = kuyaGetExchangeRate();

    if (
        $currencyRate
        && preg_match('/\b(convert|usd|dollar)\b/i', $question)
        && preg_match('/(?:php|peso|₱)\s*([\d,]+(?:\.\d+)?)/i', $question, $amountMatch)
    ) {
        $amount = (float) str_replace(',', '', $amountMatch[1]);
        $converted = $amount * (float) $currencyRate['rate'];
        kuyaRespond(200, [
            'success' => true,
            'answer' => sprintf(
                'PHP %s is approximately USD %.2f. Reference rate: 1 USD = PHP %.4f, dated %s.',
                number_format($amount, 2),
                $converted,
                1 / (float) $currencyRate['rate'],
                $currencyRate['date']
            ),
        ]);
    }
}

$geminiApiKey = kuyaGetGeminiApiKey();
if ($geminiApiKey === '') {
    kuyaRespond(503, [
        'success' => false,
        'message' => 'Kuya OW-J needs its Gemini API key before it can answer tourism questions.',
    ]);
}

$assets = kuyaGetAssets($conn, $question);
$context = kuyaBuildContext($assets);
$currencyContext = '';

if ($currencyRate) {
        $currencyContext = sprintf(
            "\n\nCurrent PHP-to-USD reference rate: 1 PHP = %.6f USD; 1 USD = PHP %.4f; rate date: %s.",
            (float) $currencyRate['rate'],
            1 / (float) $currencyRate['rate'],
            $currencyRate['date']
        );
}

$systemPrompt = "You are Kuya OW-J, the friendly tourism guide for the Jasaan Tourism Information Management System. "
    . "Answer only from the website data below. Be warm, concise, and helpful. You may answer in English, Filipino, or a natural mix based on the visitor's language. "
    . "Do not invent prices, hours, contact details, directions, or availability. If the information is not listed, say that it is not currently listed on the website. "
    . "For currency questions, treat the supplied rate as a reference estimate, not a merchant or bank rate.\n\n"
    . "LIVE WEBSITE DATA:\n" . $context . $currencyContext;

$geminiResponse = kuyaRequestJson(
    'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash-lite:generateContent',
    [
        'system_instruction' => [
            'parts' => [['text' => $systemPrompt]],
        ],
        'contents' => [[
            'role' => 'user',
            'parts' => [['text' => $question]],
        ]],
        'generationConfig' => [
            'temperature' => 0.2,
            'maxOutputTokens' => 320,
        ],
    ],
    [
        'Content-Type: application/json',
        'Accept: application/json',
        'x-goog-api-key: ' . $geminiApiKey,
    ],
    45
);

$answer = trim((string) ($geminiResponse['data']['candidates'][0]['content']['parts'][0]['text'] ?? ''));

if ($answer === '') {
    $status = (int) $geminiResponse['status'];
    $message = match ($status) {
        401, 403 => 'Kuya OW-J could not authenticate with Gemini. Check the server API key.',
        429 => 'Kuya OW-J has reached the Gemini free-tier limit. Please try again later.',
        default => 'Kuya OW-J is temporarily unavailable. Please try again in a moment.',
    };

    kuyaRespond(503, [
        'success' => false,
        'message' => $message,
    ]);
}

kuyaRespond(200, ['success' => true, 'answer' => $answer]);
