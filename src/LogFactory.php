<?php

namespace RPurinton\Knit2;

use Monolog\Level;
use Monolog\Handler\StreamHandler;

class LogFactory
{
    static function create($name): Log
    {
        $log = self::pushHandlers(self::createLogger($name), self::getConfigs());
        return $log;
    }

    static function pushHandlers($log, $configs): Log
    {
        foreach ($configs as $config) {
            if (substr($config['path'], 0, 8) !== 'https://') {
                self::validateConfig($config);
                $path = self::validatePath($config['path']);
                $level = self::validateLevel($config['level']);
                $log->pushHandler(new StreamHandler($path, $level));
            } else $log->setWebHook($config);
        }
        return $log;
    }

    static function getBasePath(): string
    {
        // expects to be installed under vendor/discommand2/core/src
        return __DIR__ . '/..';
    }

    static function createLogger($name): Log
    {
        return new Log($name);
    }

    static function getConfigs(): array
    {
        return Config::get('logs');
    }

    static function validateConfig(array $config): void
    {
        if (!isset($config['path'], $config['level'])) {
            throw new Error('Logging path or level not defined in config/logs.json!');
        }
    }

    static function validatePath($path)
    {
        if ($path === 'php://stdout' || $path === 'php://stderr') {
            return $path;
        }
        if (substr($path, 0, 1) != '/') {
            $path = self::getBasePath() . DIRECTORY_SEPARATOR . $path;
        }
        if (!is_dir(dirname($path))) {
            shell_exec('mkdir -p ' . dirname($path));
        }
        return $path;
    }

    static function validateLevel($level)
    {
        return match ($level) {
            'DEBUG' => Level::Debug,
            'INFO' => Level::Info,
            'NOTICE' => Level::Notice,
            'WARNING' => Level::Warning,
            'ERROR' => Level::Error,
            'CRITICAL' => Level::Critical,
            'ALERT' => Level::Alert,
            'EMERGENCY' => Level::Emergency,
            default => throw new Error('Invalid logging level ($level) set in config/logs.json!'),
        };
    }
}
