<?php

namespace Sportsante86\Sapa\Outils;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\WebProcessor;

final class SapaLogger
{
    private function __construct()
    {
    }

    /**
     * Get a logger instance
     *
     * @return Logger
     */
    public static function get(): Logger
    {
        $logFilePath = FilesManager::rootDirectory() . '/logs/sapa.log';

        // Create some handlers
        $stream = new StreamHandler($logFilePath, Logger::DEBUG);

        $logger = new Logger("SAPA");
        $logger->pushHandler($stream);
        $logger->pushProcessor(new SapaVersionProcessor());
        $logger->pushProcessor(new MemoryPeakUsageProcessor());
        $logger->pushProcessor(new IntrospectionProcessor(Logger::ERROR));
        $logger->pushProcessor(
            new WebProcessor(null, ['url', 'ip', 'http_method', 'server', 'referrer', 'user_agent'])
        );

        return $logger;
    }
}