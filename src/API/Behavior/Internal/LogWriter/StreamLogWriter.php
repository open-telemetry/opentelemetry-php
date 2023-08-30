<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Behavior\Internal\LogWriter;

class StreamLogWriter implements LogWriterInterface
{
    private $stream;

    public function __construct(string $destination)
    {
        $stream = fopen($destination, 'a');
        if ($stream) {
            $this->stream = $stream;
        } else {
            throw new \RuntimeException(sprintf('Unable to open %s for writing', $destination));
        }
    }

    public function write($level, string $message, array $context): void
    {
        fwrite($this->stream, Formatter::format($level, $message, $context));
    }
}
