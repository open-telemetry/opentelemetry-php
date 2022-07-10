<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Client;

use Psr\Http\Client\ClientInterface;

interface ResolverInterface
{
    public function resolvePsrClient(): ClientInterface;
}
