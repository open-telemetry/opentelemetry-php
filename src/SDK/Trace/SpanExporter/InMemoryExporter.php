<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\SpanExporter;

use ArrayObject;
use OpenTelemetry\SDK\Trace\Behavior\SpanExporterTrait;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

class InMemoryExporter implements SpanExporterInterface
{
    use SpanExporterTrait;

    private ArrayObject $storage;

    public function __construct(?ArrayObject $storage = null)
    {
        $this->storage = $storage ?? new ArrayObject();
    }

    protected function doExport(iterable $spans): bool
    {
        foreach ($spans as $span) {
            $this->storage[] = $span;
        }

        return true;
    }

    public function getSpans(): array
    {
        return (array) $this->storage;
    }

    public function getStorage(): ArrayObject
    {
        return $this->storage;
    }
}
