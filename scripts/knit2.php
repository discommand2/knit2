#!/usr/bin/env php
<?php

namespace RPurinton\Knit2;

use React\EventLoop\Loop;
use RPurinton\Knit2\{
    Consumers\Knit2\Callback,
    RabbitMQ\Consumer,
    Consumers\Knit2,
};

$worker_id = $argv[1] ?? 0;

// enable all errors for debugging
error_reporting(E_ALL);
ini_set('display_errors', '1');

try {
    require_once __DIR__ . '/../Composer.php';
    $log = LogFactory::create('Knit2-' . $worker_id) or throw new Error('failed to create log');
    set_exception_handler(function ($e) use ($log) {
        $log->error($e->getMessage(), ['trace' => $e->getTrace()]);
        exit(1);
    });
} catch (\Exception $e) {
    echo ('Fatal Exception ' . $e->getMessage() . '\n');
    exit(1);
} catch (\Throwable $e) {
    echo ('Fatal Throwable ' . $e->getMessage() . '\n');
    exit(1);
} catch (\Error $e) {
    echo ('Fatal Error ' . $e->getMessage() . '\n');
    exit(1);
}

$loop = Loop::get();
$mq = new Consumer($log, $loop) or throw new Error('failed to create RabbitMQ consumer');
$knit2 = new Knit2([
    'log' => $log,
    'loop' => $loop,
    'mq' => $mq,
    'callback' => new Callback([
        'log' => $log,
        'mq' => $mq,
    ]),
]) or throw new Error('failed to create Knit2');
$knit2->init() or throw new Error('failed to initialize Knit2');
$loop->addSignal(SIGINT, function () use ($loop, $log) {
    $log->info('SIGINT received, exiting...');
    $loop->stop();
});
$loop->addSignal(SIGTERM, function () use ($loop, $log) {
    $log->info('SIGTERM received, exiting...');
    $loop->stop();
});
