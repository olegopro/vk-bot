<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Сервис для организованного логирования в системе.
 *
 * Класс предоставляет метод для записи логов в файлы, организованные по каналам и аккаунтам.
 * Каждый аккаунт получает собственную директорию для логов, а внутри неё логи разделяются по каналам.
 * Поддерживает разные уровни логирования на основе предоставленного контекста.
 *
 * @implements LoggingServiceInterface
 */
class LoggingService implements LoggingServiceInterface
{
    /**
     * Записывает сообщение лога в указанный канал для определенного аккаунта.
     *
     * Метод создает индивидуальные файлы логов для каждого канала внутри директории аккаунта.
     * Уровень логирования автоматически определяется на основе содержимого контекста:
     * - Если контекст содержит ключ 'exception', используется уровень 'error'
     * - В остальных случаях используется уровень 'info'
     *
     * @param string $channel Название канала логирования (используется как имя файла лога)
     * @param string $account_name Имя аккаунта (используется как имя директории)
     * @param string $message Сообщение для записи в лог
     * @param array $context Дополнительный контекст для записи в лог (ассоциативный массив)
     *
     * @return void
     *
     * @example
     * // Запись обычного информационного сообщения
     * $loggingService->log('account_newsfeed', 'user123', 'Получены данные ленты', ['count' => 15]);
     *
     * @example
     * // Запись сообщения об ошибке
     * $loggingService->log('account_task_likes', 'user123', 'Ошибка при выполнении задачи',
     *                      ['exception' => $e->getMessage(), 'task_id' => 42]);
     */
    public function log($channel, $account_name, $message, $context = [])
    {
        $logPath = storage_path('logs') . '/' . $account_name;

        // Создаем папку, если она не существует
        if (!file_exists($logPath)) {
            mkdir($logPath, 0777, true);
        }

        $logFile = $logPath . '/' . $channel . '.log';

        // Устанавливаем уровень логирования в зависимости от ключей в $context
        $level = match (true) {
            array_key_exists('exception', $context) => 'error',
            default => 'info',
        };

        $handler = new StreamHandler($logFile, Logger::toMonologLevel($level));
        $monolog = new Logger($channel, [$handler]);

        $monolog->log($level, $message, $context);
    }
}
