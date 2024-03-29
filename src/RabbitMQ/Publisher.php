<?php

namespace RPurinton\Knit2\RabbitMQ;

use RPurinton\Knit2\{Config, Error, Log};
use Bunny\{Client, Channel, Exception\BunnyException};
use stdClass;

class Publisher
{
    const BROADCAST_QUEUE = 'broadcast';
    protected ?Client $client = null;
    protected ?Channel $channel = null;

    public function __construct(protected Log $log)
    {
        $this->client = new Client(Config::get('rabbitmq')) or throw new Error('Failed to establish the client');
        $this->client = $this->client->connect() or throw new Error('Failed to establish the connection');
        $this->channel = $this->client->channel() or throw new Error('Failed to establish the channel');
    }

    public function queueDeclare($queue, $broadcast = false): bool
    {
        $this->channel->queueDeclare($queue, false, false, false, true) or throw new Error('Failed to declare the queue');
        if ($broadcast) $this->channel->queueBind($queue, 'broadcast') or throw new Error('Failed to bind the queue');
        return true;
    }

    public function publish(string $queue, array|stdClass $data): bool
    {
        $result = false;
        try {
            $exchange = '';
            $result = $this->channel->publish(json_encode($data), [], $exchange, $queue) or throw new Error('Failed to publish the message');
        } catch (\Throwable $e) {
            $this->log->error($e->getMessage(), ['trace' => $e->getTrace()]);
        } catch (\Error $e) {
            $this->log->error($e->getMessage(), ['trace' => $e->getTrace()]);
        } catch (\Exception $e) {
            $this->log->error($e->getMessage(), ['trace' => $e->getTrace()]);
        } catch (BunnyException $e) {
            $this->log->error($e->getMessage(), ['trace' => $e->getTrace()]);
        }
        return $result;
    }

    public function disconnect(): bool
    {
        if (isset($this->channel) && $this->channel) {
            $this->channel->close();
        }
        if (isset($this->client) && $this->client) {
            $this->client->disconnect();
        }
        return true;
    }

    public function __destruct()
    {
        $this->disconnect() or throw new Error('Failed to disconnect from RabbitMQ');
    }
}
