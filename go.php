<?php

error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');

require_once __DIR__.'/vendor/autoload.php';
$config = json_decode(file_get_contents(__DIR__.'/config.json'), true);

$server = new \Gjh\Queue\Server();
$server->setConfig($config);
$server->start();
