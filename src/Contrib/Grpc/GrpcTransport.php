<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Grpc;

use function array_change_key_case;
use BadMethodCallException;
use Grpc\Call;
use Grpc\Channel;
use const Grpc\OP_RECV_INITIAL_METADATA;
use const Grpc\OP_RECV_MESSAGE;
use const Grpc\OP_RECV_STATUS_ON_CLIENT;
use const Grpc\OP_SEND_CLOSE_FROM_CLIENT;
use const Grpc\OP_SEND_INITIAL_METADATA;
use const Grpc\OP_SEND_MESSAGE;
use const Grpc\STATUS_OK;
use Grpc\Timeval;
use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Common\Future\NullCancellation;
use RuntimeException;
use Throwable;
use UnexpectedValueException;

/**
 * @internal
 */
final class GrpcTransport implements TransportInterface
{
    //@see protobuf *ServiceClient
    private const METHODS = [
        Signals::TRACE => '/opentelemetry.proto.collector.trace.v1.TraceService/Export',
        Signals::METRICS => '/opentelemetry.proto.collector.metrics.v1.MetricsService/Export',
        Signals::LOGS => '/opentelemetry.proto.collector.logs.v1.LogsService/Export',
    ];
    private array $metadata;
    private Channel $channel;
    private string $method;
    private bool $closed = false;

    public function __construct(
        string $endpoint,
        array $opts,
        string $method,
        array $headers = []
    ) {
        $this->channel = new Channel($endpoint, $opts);
        $this->method = $method;
        $this->metadata = $this->formatMetadata(array_change_key_case($headers));
    }

    public function send(string $payload, string $contentType, ?CancellationInterface $cancellation = null): FutureInterface
    {
        if ($this->closed) {
            return new ErrorFuture(new BadMethodCallException('Transport closed'));
        }
        if ($contentType !== 'application/x-protobuf') {
            return new ErrorFuture(new UnexpectedValueException(sprintf('Unsupported content type "%s", grpc transport supports only application/x-protobuf', $contentType)));
        }

        $call = new Call($this->channel, $this->method, Timeval::infFuture());

        $cancellation ??= new NullCancellation();
        $cancellationId = $cancellation->subscribe(static fn (Throwable $e) => $call->cancel());

        try {
            $event = $call->startBatch([
                OP_SEND_INITIAL_METADATA => $this->metadata,
                OP_SEND_MESSAGE => ['message' => $payload],
                OP_SEND_CLOSE_FROM_CLIENT => true,
                OP_RECV_INITIAL_METADATA => true,
                OP_RECV_STATUS_ON_CLIENT => true,
                OP_RECV_MESSAGE => true,
            ]);
        } catch (Throwable $e) {
            return new ErrorFuture($e);
        } finally {
            $cancellation->unsubscribe($cancellationId);
        }

        if ($event->status->code === STATUS_OK) {
            return new CompletedFuture($event->message);
        }

        return new ErrorFuture(new RuntimeException($event->status->details, $event->status->code));
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;
        $this->channel->close();

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
        return array_map(fn ($value) => is_array($value) ? $value : [$value], $metadata);
    }

    public static function method(string $signal): string
    {
        if (!array_key_exists($signal, self::METHODS)) {
            throw new UnexpectedValueException('Method not defined for signal: ' . $signal);
        }

        return self::METHODS[$signal];
    }
}
