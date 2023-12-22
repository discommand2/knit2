<?php

namespace RPurinton\Knit2\Consumers\Knit2;

use Bunny\{
    Channel,
    Message as RabbitMessage
};
use RPurinton\Knit2\{
    Error,
    Log,
};

class Callback
{
    private Log $log;

    public function __construct(array $config)
    {
        $this->log = $config['log'];
    }

    public function callback(RabbitMessage $message, Channel $channel): bool
    {
        $this->log->debug('received message', ['message' => $message]);
        $this->log->info('Callback::callback()');
        $content = json_decode($message->content, true);
        $this->route($content ?? []) or throw new Error('failed to route message');
        $channel->ack($message);
        return true;
    }

    private function route(array $content): bool
    {
        return true;
    }
}
