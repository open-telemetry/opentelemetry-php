<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior\Internal\LogWriter;

use Psr\Log\LoggerInterface;

class Psr3LogWriter implements LogWriterInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function write($level, string $message, array $context): void
    {
        $this->logger->log($level, $message, $context);
    }
}
