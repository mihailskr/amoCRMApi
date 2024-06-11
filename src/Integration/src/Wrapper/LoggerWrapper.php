<?php

declare(strict_types=1);

namespace Integration\Wrapper;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LoggerWrapper
{
    private Logger $logger;
    /** Лог воркера хуков */
    public const HOOK_WORKER_LOG = 'hook_worker.log';
    /** Лог команды обновления токенов */
    public const UPDATE_TOKENS_COMMAND_LOG = 'update_tokens_command.log';
    /** Лог команды синхронизации сделок при авторизации */
    public const SYNC_LEADS_LOG = 'sync_leads_worker.log';

    public function __construct(string $logName)
    {
        $this->logger = new Logger($logName);
        $this->logger->pushHandler(new StreamHandler(getenv('MONOLOG_LOGGER_PATH') . '/' . $logName, Logger::INFO));
    }

    /**
     * Получение клиета логгера
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }
}
