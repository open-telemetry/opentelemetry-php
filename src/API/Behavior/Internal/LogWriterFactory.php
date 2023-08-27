<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior\Internal;

use OpenTelemetry\API\Behavior\Internal\LogWriter\ErrorLogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Behavior\Internal\LogWriter\NoopLogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\Psr3LogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\StreamLogWriter;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\LoggerHolder;

class LogWriterFactory
{
    private const OTEL_PHP_LOG_DESTINATION = 'OTEL_PHP_LOG_DESTINATION';

    public function create(): LogWriterInterface
    {
        $dest = Globals::configurationResolver()->getString(self::OTEL_PHP_LOG_DESTINATION);
        //we might not have an SDK, so attempt to get from environment
        if (!$dest) {
            $dest = array_key_exists(self::OTEL_PHP_LOG_DESTINATION, $_SERVER)
                ? $_SERVER[self::OTEL_PHP_LOG_DESTINATION]
                : getenv(self::OTEL_PHP_LOG_DESTINATION);
        }
        if (!$dest) {
            $dest = ini_get(self::OTEL_PHP_LOG_DESTINATION);
        }
        $logger = LoggerHolder::get();

        switch ($dest) {
            case 'none':
                return new NoopLogWriter();
            case 'stderr':
                return new StreamLogWriter('php://stderr');
            case 'stdout':
                return new StreamLogWriter('php://stdout');
            case 'psr3':
                if ($logger) {
                    return new Psr3LogWriter($logger);
                }
                error_log('OpenTelemetry: cannot use OTEL_PHP_LOG_DESTINATION=psr3 without providing a PSR-3 logger');
                //default to error log
                return new ErrorLogWriter();
            case 'error_log':
                return new ErrorLogWriter();
            default:
                if ($logger) {
                    return new Psr3LogWriter($logger);
                }

                return new ErrorLogWriter();
        }
    }
}
