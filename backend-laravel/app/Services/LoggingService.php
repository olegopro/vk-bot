<?php
namespace App\Services;

use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggingService implements LoggingServiceInterface
{
    private $level;

    public function log($channel, $account_name, $message, $context = [])
    {
        $logPath = storage_path('logs') . '/' . $account_name;

        // Создаем папку, если она не существует
        if (!file_exists($logPath)) {
            mkdir($logPath, 0777, true);
        }

        $logFile = $logPath . '/' . $channel . '.log';

        // Устанавливаем уровень логирования в зависимости от ключей в $context
        $this->level = match (true) {
            array_key_exists('exception', $context) => 'error',
            default => 'info',
        };

        $handler = new StreamHandler($logFile, Logger::toMonologLevel($this->level));
        $monolog = new Logger($channel, [$handler]);

        $monolog->log($this->level, $message, $context);
    }
}
