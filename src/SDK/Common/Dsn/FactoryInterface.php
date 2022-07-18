<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Dsn;

interface FactoryInterface
{
    public function fromArray(array $dsn): DsnInterface;
}
