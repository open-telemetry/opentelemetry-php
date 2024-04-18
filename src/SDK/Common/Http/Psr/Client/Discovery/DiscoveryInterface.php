<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery;

use Psr\Http\Client\ClientInterface;

interface DiscoveryInterface
{
    public function available(): bool;
    public function create(mixed $options): ClientInterface;
}
