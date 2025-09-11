<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use ArrayObject;
use OpenTelemetry\SDK\Common\Export\InMemoryStorageManager;
use OpenTelemetry\SDK\Common\Export\TransportInterface;
use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\CompletedFuture;
use OpenTelemetry\SDK\Common\Future\FutureInterface;

/**
 * @internal
 *
 * @template-implements TransportInterface<"memory/json">
 */
class MemoryTransport implements TransportInterface
{
    private ArrayObject $storage;

    public function __construct()
    {
        $this->storage = InMemoryStorageManager::metrics();
    }

    public function contentType(): string
    {
        return ContentTypes::JSON;
    }

    public function send(string $payload, ?CancellationInterface $cancellation = null): FutureInterface
    {
        $this->storage->append($payload);

        return new CompletedFuture(null);
    }

    public function shutdown(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }

    public function forceFlush(?CancellationInterface $cancellation = null): bool
    {
        return true;
    }
}
