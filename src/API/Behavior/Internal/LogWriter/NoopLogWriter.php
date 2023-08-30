<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior\Internal\LogWriter;

class NoopLogWriter implements LogWriterInterface
{
    public function write($level, string $message, array $context): void
    {
        //do nothing
    }
}
