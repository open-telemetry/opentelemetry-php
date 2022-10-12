<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;

interface OtlpTransportFactoryInterface
{
    public function withSignal(string $signal): TransportFactoryInterface;

    public function withProtocol(string $protocol): TransportFactoryInterface;
}
