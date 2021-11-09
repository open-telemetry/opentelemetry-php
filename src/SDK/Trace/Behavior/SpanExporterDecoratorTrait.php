<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Behavior;

use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

trait SpanExporterDecoratorTrait
{
    protected SpanExporterInterface $decorated;

    /**
     * @param iterable<SpanDataInterface> $spans
     * @return int
     * @psalm-return SpanExporterInterface::STATUS_*
     */
    public function export(iterable $spans): int
    {
        $response = $this->decorated->export(
            $this->beforeExport($spans)
        );
        $this->afterExport($spans, $response);

        return $response;
    }

    abstract protected function beforeExport(iterable $spans): iterable;

    abstract protected function afterExport(iterable $spans, int $exporterResponse): void;

    public function shutdown(): bool
    {
        return $this->decorated->shutdown();
    }

    public function forceFlush(): bool
    {
        return $this->decorated->forceFlush();
    }

    public function setDecorated(SpanExporterInterface $decorated): void
    {
        $this->decorated = $decorated;
    }
}
