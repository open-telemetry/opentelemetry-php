<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior\Internal;

use OpenTelemetry\API\Behavior\Internal\LogWriter\ErrorLogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\LogWriterInterface;
use OpenTelemetry\API\Behavior\Internal\LogWriter\NoopLogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\Psr3LogWriter;
use OpenTelemetry\API\Behavior\Internal\LogWriter\StreamLogWriter;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\Config\Configuration;
use OpenTelemetry\Config\KnownValues;
use OpenTelemetry\Config\Variables;

class LogWriterFactory
{
    public function create(): LogWriterInterface
    {
        $dest = (new Configuration())->getEnum(Variables::OTEL_PHP_LOG_DESTINATION);
        $logger = LoggerHolder::get();

        switch ($dest) {
            case KnownValues::VALUE_NONE:
                return new NoopLogWriter();
            case KnownValues::VALUE_STDERR:
                return new StreamLogWriter('php://stderr');
            case KnownValues::VALUE_STDOUT:
                return new StreamLogWriter('php://stdout');
            case KnownValues::VALUE_PSR3:
                if ($logger) {
                    return new Psr3LogWriter($logger);
                }
                error_log('OpenTelemetry: cannot use OTEL_PHP_LOG_DESTINATION=psr3 without providing a PSR-3 logger');

                //default to error log
                return new ErrorLogWriter();
            case KnownValues::VALUE_ERROR_LOG:
                return new ErrorLogWriter();
            default:
                if ($logger) {
                    return new Psr3LogWriter($logger);
                }

                return new ErrorLogWriter();
        }
    }
}
