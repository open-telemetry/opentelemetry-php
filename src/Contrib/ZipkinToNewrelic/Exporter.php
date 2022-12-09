<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\ZipkinToNewrelic;

use JsonException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Trace\Behavior\UsesSpanConverterTrait;
use OpenTelemetry\SDK\Trace\SpanExporterInterface;
use Throwable;

/**
 * Class ZipkinExporter - implements the export interface for data transfer via Zipkin protocol
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

    private TransportInterface $transport;

    public function __construct(
        TransportInterface $transport,
        SpanConverter $spanConverter = null
    ) {
        $this->transport = $transport;
        $this->setSpanConverter($spanConverter ?? new SpanConverter());
    }

    public static function create(
        string $endpointUrl,
        string $licenseKey
    ): self {
        $transport = PsrTransportFactory::discover()->create($endpointUrl, 'application/json', [
            'Api-Key' => $licenseKey,
            'Data-Format' => 'zipkin',
            'Data-Format-Version' => '2',
        ]);

        return new self($transport);
    }

    /**
     * @throws JsonException
     */
    protected function serializeTrace(iterable $spans): string
    {
        return json_encode(
            $this->getSpanConverter()->convert($spans),
            JSON_THROW_ON_ERROR
        );
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
