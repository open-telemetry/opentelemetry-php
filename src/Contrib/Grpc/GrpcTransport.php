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
use OpenTelemetry\API\Common\Time\ClockInterface;
use OpenTelemetry\Contrib\Otlp\ContentTypes;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Common\Future\NullCancellation;
use RuntimeException;
use Throwable;

/**
 * @internal
 *
 * @template-implements TransportInterface<"application/x-protobuf">
 */
final class GrpcTransport implements TransportInterface
{
    private readonly array $metadata;
    private readonly Channel $channel;
    private bool $closed = false;
    private Timeval $exportTimeout;

    public function __construct(
        string $endpoint,
        array $opts,
        private readonly string $method,
        array $headers = [],
        int $timeoutMillis = 500,
    ) {
        $this->channel = new Channel($endpoint, $opts);
        $this->metadata = $this->formatMetadata(array_change_key_case($headers));
        $this->exportTimeout = new Timeval($timeoutMillis * ClockInterface::MICROS_PER_MILLISECOND);
    }

    public function contentType(): string
    {
        return ContentTypes::PROTOBUF;
    }

    public function send(string $payload, ?CancellationInterface $cancellation = null): FutureInterface
    {
        if ($this->closed) {
            return new ErrorFuture(new BadMethodCallException('Transport closed'));
        }

        $call = new Call($this->channel, $this->method, $this->exportTimeout);

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
}
