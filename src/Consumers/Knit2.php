<?php

namespace RPurinton\Knit2\Consumers;


use RPurinton\Knit2\{
    Consumers\Knit2\Config,
    Consumers\Knit2\Callback,
    RabbitMQ\Consumer,
    Error,
    Log,
};

class Knit2
{
    const QUEUE = 'github-inbox';
    private Consumer $mq;
    private Callback $callback;
    private Log $log;

    public function __construct(array $config)
    {
        $this->validateConfig($config) or throw new Error('invalid config');
        $this->log = $config['log'];
        $this->log->debug('Knit2::__construct()', ['config' => $config]);
        $this->mq = $config['mq'];
        $this->callback = $config['callback'];
    }

    private function validateConfig(array $config): bool
    {
        $requiredKeys = [
            'log' => 'RPurinton\Knit2\Log',
            'loop' => 'React\EventLoop\LoopInterface',
            'mq' => 'RPurinton\Knit2\RabbitMQ\Consumer',
            'callback' => 'RPurinton\Knit2\Consumers\Knit2\Callback',
        ];
        foreach ($requiredKeys as $key => $class) {
            if (!array_key_exists($key, $config)) throw new Error('missing required key ' . $key);
            if (!is_a($config[$key], $class)) throw new Error('invalid type for ' . $key);
        }
        return true;
    }

    public function init(): void
    {
        $this->log->debug('Knit2::init()');
        $this->mq->consume(self::QUEUE, $this->callback->callback(...)) or throw new Error('failed to consume');
        $this->log->info('Knit2 is ready!');
    }
}
