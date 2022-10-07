<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Grpc;

use function array_change_key_case;
use BadMethodCallException;
use const Grpc\STATUS_OK;
use Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceRequest;
use Opentelemetry\Proto\Collector\Trace\V1\TraceServiceClient;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Common\Future\NullCancellation;
use RuntimeException;
use function sprintf;
use Throwable;
use UnexpectedValueException;

/**
 * @internal
 */
final class GrpcTransport implements TransportInterface
{
    private array $headers;
    private TraceServiceClient $client;

    private bool $closed = false;

    public function __construct(
        TraceServiceClient $client,
        array $headers = []
    ) {
        $this->client = $client;
        $this->headers = array_change_key_case($headers);
    }

    /**
     * @todo Possibly inefficient to convert payload to/from string, can we refactor to use ExportTraceServiceRequest?
     */
    public function send(string $payload, string $contentType, ?CancellationInterface $cancellation = null): FutureInterface
    {
        if ($this->closed) {
            return new ErrorFuture(new BadMethodCallException('Transport closed'));
        }
        if ($contentType !== 'application/x-protobuf') {
            return new ErrorFuture(new UnexpectedValueException(sprintf('Unsupported content type "%s", grpc transport supports only application/x-protobuf', $contentType)));
        }

        $request = new ExportTraceServiceRequest();

        try {
            $request->mergeFromString($payload);
        } catch (\Exception $e) {
            return new ErrorFuture($e);
        }

        $call = $this->client->Export($request, $this->formatMetadata($this->headers));

        $cancellation ??= new NullCancellation();
        $cancellationId = $cancellation->subscribe(static fn (Throwable $e) => $call->cancel());

        try {
            // @var \Opentelemetry\Proto\Collector\Trace\V1\ExportTraceServiceResponse $response
            [$response, $status] = $call->wait();
        } catch (Throwable $e) {
            return new ErrorFuture($e);
        } finally {
            $cancellation->unsubscribe($cancellationId);
        }

        if ($status->code === STATUS_OK) {
            return new CompletedFuture($response->serializeToString());
        }

        return new ErrorFuture(new RuntimeException($status->details, $status->code));
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;
        $this->client->close();

        return true;
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return !$this->closed;
    }

    /**
     * @link https://github.com/grpc/grpc/blob/7e7a48f863218f39a1767e1c2b957ca8e4789272/src/php/tests/interop/interop_client.php#L525
     */
    private function formatMetadata(array $metadata): array
    {
        return array_map(fn ($value) => [$value], $metadata);
    }
}
