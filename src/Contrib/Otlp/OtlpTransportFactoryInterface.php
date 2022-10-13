<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;

interface OtlpTransportFactoryInterface extends TransportFactoryInterface
{
    public function withSignal(string $signal): OtlpTransportFactoryInterface;

    public function withProtocol(string $protocol): OtlpTransportFactoryInterface;
}
