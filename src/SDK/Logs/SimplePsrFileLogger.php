<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use Psr\Log\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use ReflectionClass;
use Throwable;

class SimplePsrFileLogger implements LoggerInterface
{
    use LoggerTrait;

    private const DEFAULT_LOGGER_NAME = 'otel';

    private static ?array $logLevels = null;

    public function __construct(
        private readonly string $filename,
        private readonly string $loggerName = self::DEFAULT_LOGGER_NAME,
    ) {
    }

    /**
     * @psalm-suppress MoreSpecificImplementedParamType
     */
    public function log($level, $message, array $context = []): void
    {
        $level = strtolower((string) $level);

        if (!in_array($level, self::getLogLevels(), true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid Log level: "%s"', $level)
            );
        }

        file_put_contents($this->filename, $this->formatLog((string) $level, (string) $message, $context), FILE_APPEND);
    }

    /**
     * @return string
     */
    private function formatLog(string $level, string $message, array $context = []): string
    {
        try {
            $encodedContext = json_encode($context, JSON_THROW_ON_ERROR);
        } catch (Throwable $t) {
            $encodedContext = sprintf('(Could not encode context: %s)', $t->getMessage());
        }

        return sprintf(
            '[%s] %s %s: %s %s%s',
            date(DATE_RFC3339_EXTENDED),
            $this->loggerName,
            $level,
            $message,
            $encodedContext,
            PHP_EOL
        );
    }

    /**
     * @return array
     */
    private static function getLogLevels(): array
    {
        return self::$logLevels ?? self::$logLevels = (new ReflectionClass(LogLevel::class))->getConstants();
    }
}
