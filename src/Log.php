<?php

namespace RPurinton\Knit2;

class Log extends \Monolog\Logger
{
    private array $webHooks = [];

    public function __construct(private string $myName)
    {
        parent::__construct($myName);
    }

    public function setWebHook(array $config): void
    {
        $this->webHooks[] = $config;
    }

    private function levelCompare($level1, $level2): bool
    {
        $levels = [
            'debug' => 100,
            'info' => 200,
            'notice' => 250,
            'warning' => 300,
            'error' => 400,
            'critical' => 500,
            'alert' => 550,
            'emergency' => 600,
        ];
        return $levels[$level1] >= $levels[$level2];
    }

    public function debug(string|\Stringable $message, array $context = []): void
    {
        parent::debug($message, $context);
    }

    public function info(string|\Stringable $message, array $context = []): void
    {
        parent::info($message, $context);
    }

    public function notice(string|\Stringable $message, array $context = []): void
    {
        parent::notice($message, $context);
    }

    public function warning(string|\Stringable $message, array $context = []): void
    {
        parent::warning($message, $context);
    }

    public function error(string|\Stringable $message, array $context = []): void
    {
        parent::error($message, $context);
    }

    public function critical(string|\Stringable $message, array $context = []): void
    {
        parent::critical($message, $context);
    }

    public function alert(string|\Stringable $message, array $context = []): void
    {
        parent::alert($message, $context);
    }

    public function emergency(string|\Stringable $message, array $context = []): void
    {
        parent::emergency($message, $context);
    }

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        parent::log($level, $message, $context);
    }

    public function sendWebhook($level, string|\Stringable $message, array $context = []): void
    {
        foreach ($this->webHooks as $webHook) $this->send($webHook, $level, $message, $context);
    }

    private function send(array $webHook, $level, string|\Stringable $message, array $context = []): void
    {
        if (!$this->levelCompare($level, $webHook['level'])) return;
        $url = $webHook['path'];
        $data = [
            'username' => $this->myName,
            'embeds' => [
                [
                    'title' => $level . ': ' . $message,
                    'color' => $this->getColor($level),
                ]
            ]
        ];
        foreach ($context as $key => $value) {
            $data['embeds'][0]['fields'][] = [
                'name' => $key,
                'value' => print_r($value, true),
                'inline' => true,
            ];
        }
        $data = json_encode($data);
        HTTPS::post($url, ["Content-Type: application/json"], $data);
    }

    private function getColor($level): string
    {
        switch ($level) {
            case 'debug':
                return '#00ff00';
            case 'info':
                return '#0000ff';
            case 'notice':
                return '#0000ff';
            case 'warning':
                return '#ffff00';
            case 'error':
                return '#ff0000';
            case 'critical':
                return '#ff0000';
            case 'alert':
                return '#ff0000';
            case 'emergency':
                return '#ff0000';
            default:
                return '#ffffff';
        }
    }
}
