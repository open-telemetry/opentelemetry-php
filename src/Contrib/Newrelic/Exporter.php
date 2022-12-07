<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Newrelic;

use JsonException;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Throwable;

/**
 * Class NewrelicExporter - implements the export interface for data transfer via Newrelic protocol
 * @package OpenTelemetry\Exporter
 *
 * This is an experimental, non-supported exporter.
 * It will send PHP Otel trace data end to end across the internet to a functional backend.
 * Needs a license key to connect.  For a free account/key, go to: https://newrelic.com/signup/
 */
class Exporter implements SpanExporterInterface
{
    use LogsMessagesTrait;
    use UsesSpanConverterTrait;

    public const DATA_FORMAT_VERSION_DEFAULT = '1';

    private TransportInterface $transport;
    private string $endpointUrl;

    public function __construct(
        TransportInterface $transport,
        string $endpointUrl,
        SpanConverter $spanConverter = null
    ) {
        $this->endpointUrl = $endpointUrl;
        $this->transport = $transport;
        $this->setSpanConverter($spanConverter ?? new SpanConverter());
    }

    /**
     * @throws JsonException
     */
    protected function serializeTrace(iterable $spans): string
    {
        return json_encode($this->convert($spans), JSON_THROW_ON_ERROR);
    }

    private function convert(iterable $spans): array
    {
        $commonAttributes = ['attributes' => [
            'host' => $this->endpointUrl, ]];

        return [[ 'common' => $commonAttributes,
            'spans' => $this->getSpanConverter()->convert($spans), ]];
    }

    public function export(iterable $batch, ?CancellationInterface $cancellation = null): FutureInterface
    {
        return $this->transport
            ->send($this->serializeTrace($batch), $cancellation)
            ->map(static fn (): bool => true)
            ->catch(static function (Throwable $throwable): bool {
                self::logError('Export failure', ['exception' => $throwable]);

                return false;
            });
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return $this->transport->shutdown($cancellation);
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return $this->transport->forceFlush($cancellation);
    }
}
