<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export;

use OpenTelemetry\SDK\Common\Future\CancellationInterface;
use OpenTelemetry\SDK\Common\Future\FutureInterface;

interface TransportInterface
{
    public const CONTENT_TYPE_PROTOBUF = 'application/x-protobuf';

    public function send(string $payload, string $contentType, ?CancellationInterface $cancellation = null): FutureInterface;

    public function shutdown(?CancellationInterface $cancellation = null): bool;

    public function forceFlush(?CancellationInterface $cancellation = null): bool;
}
