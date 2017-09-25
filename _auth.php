<?php

require "env.php";

$shop = $_GET['shop'];
$hmac = $_GET['hmac'];


$str = '';
$params = $_GET;
unset($params['hmac']);
ksort($params);
$str = http_build_query($params);

$digest = hash_hmac('sha256', $str, API_SECRET);

if ($digest != $hmac) {

}