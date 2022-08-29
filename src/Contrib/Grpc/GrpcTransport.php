<?php

/** @noinspection PhpComposerExtensionStubsInspection */

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Grpc;

use BadMethodCallException;
use UnexpectedValueException;
use function array_change_key_case;
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
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\ErrorFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;
use OpenTelemetry\SDK\Common\Future\NullCancellation;
use RuntimeException;
use function sprintf;
use Throwable;

/**
 * @internal
 */
final class GrpcTransport implements TransportInterface
{
    private Channel $channel;
    private string $method;
    private array $headers;

    private bool $closed = false;

    public function __construct(
        string $endpoint,
        array $opts,
        string $method,
        array $headers = []
    ) {
        $this->channel = new Channel($endpoint, $opts);
        $this->method = $method;
        $this->headers = array_change_key_case($headers);
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
                OP_SEND_INITIAL_METADATA => $this->headers,
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
}
