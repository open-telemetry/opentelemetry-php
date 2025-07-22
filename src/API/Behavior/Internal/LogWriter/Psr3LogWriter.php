<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior\Internal\LogWriter;

use Psr\Log\LoggerInterface;

class Psr3LogWriter implements LogWriterInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    #[\Override]
    public function write($level, string $message, array $context): void
    {
        $this->logger->log($level, $message, $context);
    }
}
