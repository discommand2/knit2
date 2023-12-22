<?php

use RPurinton\Knit2\{
    RabbitMQ\Publisher,
    LogFactory,
};

$json = json_decode(file_get_contents('php://input'), true);
if (is_null($json)) {
    http_response_code(400);
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';
$log = LogFactory::create('Knit2-WebHook') or throw new Error('failed to create log');
(new Publisher($log))->publish('github-inbox', $json);

echo ("Thanks!");
