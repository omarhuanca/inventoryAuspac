<?php
$url = 'http://devesdc.sandbox.taxcore.online:8888/swagger/v1/swagger.json';
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => false,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
echo "HTTP $code" . PHP_EOL;
echo $resp . PHP_EOL;
