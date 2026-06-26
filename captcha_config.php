<?php
define('RECAPTCHA_SITE_KEY', '6LdQqRwtAAAAAEKtf5--iilJ9UC7BRkflUBS6rtT');
define('RECAPTCHA_SECRET_KEY', '6LdQqRwtAAAAANfsJAwxquGmcfKdwy8UPyvJ2t0j');

function isLocalNetwork() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return (strpos($ip, '127.0.0.1') === 0 || strpos($ip, '192.168.') === 0 || strpos($ip, '10.0.') === 0 || strpos($ip, '172.16.') === 0 || strpos($ip, '::1') === 0);
}

function verifyCaptcha($token) {
    if (isLocalNetwork()) return true;
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $data = ['secret' => RECAPTCHA_SECRET_KEY, 'response' => $token];
    $options = ['http' => ['header' => "Content-type: application/x-www-form-urlencoded\r\n", 'method' => 'POST', 'content' => http_build_query($data)]];
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $response = json_decode($result, true);
    return $response['success'] === true;
}
?>