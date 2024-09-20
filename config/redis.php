<?php

require 'D:\xampp\htdocs\api\vendor/autoload.php';


function getRedisConnection()
{
    $client = new Predis\Client([
        'scheme' => 'tcp',
        'host' => '127.0.0.1',
        'port' => 6379,
    ]);

    return $client;
}