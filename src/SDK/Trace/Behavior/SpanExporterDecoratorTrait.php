<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\Behavior;

use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;

trait SpanExporterDecoratorTrait
{
    protected SpanExporterInterface $decorated;

    /**
     * @param iterable<SpanDataInterface> $spans
     * @return FutureInterface<int>
     */
    public function export(iterable $spans, ?CancellationInterface $cancellation = null): FutureInterface
    {
        $spans = $this->beforeExport($spans);
        $response = $this->decorated->export($spans, $cancellation);
        $response->map(fn (int $result) => $this->afterExport($spans, $result));

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
