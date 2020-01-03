<?php

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

require_once __DIR__.'/vendor/autoload.php';

$cfg = json_decode(file_get_contents(__DIR__.'/config.json'));

$server = new \Gjh\Queue\Server();
$server->start();
