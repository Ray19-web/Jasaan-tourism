<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

function sendExchangeRateResponse(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload);
    exit;
}

function fetchExchangeRate(string $url, array $headers): ?string
{
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return is_string($response) && $status === 200 ? $response : null;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => implode("\r\n", $headers),
            'timeout' => 10,
            'ignore_errors' => true,
        ],
    ]);

    $response = @file_get_contents($url, false, $context);
    return is_string($response) ? $response : null;
}

$response = fetchExchangeRate(
    'https://api.frankfurter.dev/v2/rate/php/usd',
    [
        'Accept: application/json',
        'User-Agent: JasaanTourism/1.0 (+http://localhost/jasaan-tourism)',
    ]
);

if ($response === null) {
    sendExchangeRateResponse(503, [
        'success' => false,
        'message' => 'The currency rate is temporarily unavailable.',
    ]);
}

$data = json_decode($response, true);
$rate = is_array($data) ? (float) ($data['rate'] ?? 0) : 0;

if ($rate <= 0 || empty($data['date'])) {
    sendExchangeRateResponse(503, [
        'success' => false,
        'message' => 'The currency rate response was invalid.',
    ]);
}

sendExchangeRateResponse(200, [
    'success' => true,
    'base' => 'PHP',
    'quote' => 'USD',
    'rate' => $rate,
    'date' => $data['date'],
    'provider' => 'Frankfurter',
    'fetched_at' => gmdate('c'),
]);
