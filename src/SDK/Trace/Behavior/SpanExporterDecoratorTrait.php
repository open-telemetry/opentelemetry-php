<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Behavior;

use OpenTelemetry\SDK\Common\Future\CancellationInterface;
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
    public function export(iterable $spans, ?CancellationInterface $cancellation = null): int
    {
        $response = $this->decorated->export(
            $this->beforeExport($spans),
            $cancellation,
        );
        $this->afterExport($spans, $response);

        return $response;
    }

    abstract protected function beforeExport(iterable $spans): iterable;

    abstract protected function afterExport(iterable $spans, int $exporterResponse): void;

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return $this->decorated->shutdown($cancellation);
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return $this->decorated->forceFlush($cancellation);
    }

    public function setDecorated(SpanExporterInterface $decorated): void
    {
        $this->decorated = $decorated;
    }
}
