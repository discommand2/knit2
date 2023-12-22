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
        $content = json_decode($message->content, true);
        $this->log->debug('received message', ['content' => $content]);
        $this->route($content ?? []) or throw new Error('failed to route message');
        $channel->ack($message);
        return true;
    }

    private function route(array $content): bool
    {
        return true;
    }
}
