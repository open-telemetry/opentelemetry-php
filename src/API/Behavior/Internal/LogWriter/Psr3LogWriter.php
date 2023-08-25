<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior\Internal\LogWriter;

use Psr\Log\LoggerInterface;

class Psr3LogWriter implements LogWriterInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function write($level, string $message, array $context): void
    {
        $this->logger->log($level, $message, $context);
    }
}
