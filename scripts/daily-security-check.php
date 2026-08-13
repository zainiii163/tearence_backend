<?php
/**
 * Daily defensive security smoke checks for WWA (Hostinger-friendly CLI).
 * Safe: only GET/POST expected-failure probes — no exploit payloads.
 *
 * Cron (Hostinger → Advanced → Cron Jobs), once daily:
 *   /usr/bin/php /home/USER/domains/api.worldwideadverts.info/public_html/../scripts/daily-security-check.php
 * Or from Laravel root:
 *   php scripts/daily-security-check.php
 *
 * Exit 0 = pass, 1 = fail (email yourself via Hostinger cron “send to email”).
 */
$frontend = rtrim(getenv('FRONTEND_URL') ?: 'https://worldwideadverts.info', '/');
$api = rtrim(getenv('API_URL') ?: 'https://api.worldwideadverts.info', '/');
$apiV1 = $api . '/api/v1';

$failures = 0;
$passed = 0;

function http_request(string $url, string $method = 'GET', ?string $body = null, array $headers = []): array
{
    $ch = curl_init($url);
    $hdrs = array_merge(['Accept: application/json,text/html,*/*'], $headers);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $hdrs,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => false,
    ]);
    if ($body !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    }
    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = (int) curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);
    if ($raw === false) {
        return ['status' => 0, 'headers' => '', 'body' => $err, 'header_map' => []];
    }
    $headerBlob = substr($raw, 0, $headerSize);
    $bodyOut = substr($raw, $headerSize);
    $map = [];
    foreach (explode("\r\n", $headerBlob) as $line) {
        if (str_contains($line, ':')) {
            [$k, $v] = explode(':', $line, 2);
            $map[strtolower(trim($k))] = trim($v);
        }
    }
    return ['status' => $status, 'headers' => $headerBlob, 'body' => substr($bodyOut, 0, 2000), 'header_map' => $map];
}

function record(string $name, bool $ok, string $detail = ''): void
{
    global $failures, $passed;
    if ($ok) {
        $passed++;
        echo "[PASS] {$name}" . ($detail !== '' ? " — {$detail}" : '') . PHP_EOL;
    } else {
        $failures++;
        echo "[FAIL] {$name}" . ($detail !== '' ? " — {$detail}" : '') . PHP_EOL;
    }
}

function expect_alive(string $name, string $url, int $min = 200, int $max = 399): void
{
    $r = http_request($url);
    record($name, $r['status'] >= $min && $r['status'] <= $max, 'HTTP ' . $r['status']);
}

function expect_gone(string $name, string $url): void
{
    $r = http_request($url);
    $status = $r['status'];
    // Debug endpoints must not return 200
    record($name, ! in_array($status, [200, 201], true), 'HTTP ' . $status);
}

echo 'Daily security check @ ' . gmdate('c') . PHP_EOL;
echo "Frontend: {$frontend}" . PHP_EOL;
echo "API: {$api}" . PHP_EOL . PHP_EOL;

$hubs = [
    'Home' => $frontend . '/',
    'Login' => $frontend . '/Login',
    'Affiliates' => $frontend . '/affiliates',
    'Books' => $frontend . '/books',
    'Vehicles' => $frontend . '/vehicles',
    'BuySell' => $frontend . '/buysell',
    'Jobs' => $frontend . '/jobs',
    'Property' => $frontend . '/property',
    'Payment' => $frontend . '/payment',
    'Business tools' => $frontend . '/business-tools',
];
foreach ($hubs as $label => $url) {
    expect_alive("Site up: {$label}", $url);
}

expect_alive('API responds', $apiV1 . '/', 200, 499);

expect_gone('No phpinfo.php', $api . '/phpinfo.php');
expect_gone('No info.php', $api . '/info.php');
expect_gone('No test.php', $api . '/test.php');
expect_gone('No proxy.php', $api . '/proxy.php');
expect_gone('No public /logs', $api . '/logs');

$cors = http_request($apiV1 . '/', 'GET', null, ['Origin: https://evil.example']);
$acao = $cors['header_map']['access-control-allow-origin'] ?? '';
record('API CORS not *', $acao !== '*', 'Access-Control-Allow-Origin=' . ($acao !== '' ? $acao : '(none)'));

$pay = http_request(
    $apiV1 . '/business-tools/purchases/1/confirm-payment',
    'POST',
    json_encode(['payment_id' => 'paid', 'payment_method' => 'paypal']),
    ['Content-Type: application/json']
);
$payOk = in_array($pay['status'], [401, 403, 404, 422], true)
    || ($pay['status'] >= 400 && ! preg_match('/"success"\s*:\s*true/', $pay['body']));
record('Payment confirm rejects fake id', $payOk, 'HTTP ' . $pay['status']);

$login = http_request(
    $apiV1 . '/login',
    'POST',
    json_encode(['email' => 'security-check@example.com', 'password' => 'invalid-password-check']),
    ['Content-Type: application/json']
);
record(
    'Login rejects bad credentials',
    in_array($login['status'], [401, 404, 422, 429], true),
    'HTTP ' . $login['status']
);

echo PHP_EOL . "Summary: {$passed} passed, {$failures} failed" . PHP_EOL;
exit($failures > 0 ? 1 : 0);
