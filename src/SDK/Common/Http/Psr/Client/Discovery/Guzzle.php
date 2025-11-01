<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery;

use GuzzleHttp\Client;
use Psr\Http\Client\ClientInterface;

class Guzzle implements DiscoveryInterface
{
    #[\Override]
    public function available(): bool
    {
        return class_exists(Client::class) && is_a(Client::class, ClientInterface::class, true);
    }

    #[\Override]
    public function create(mixed $options): ClientInterface
    {
        return new Client($options);
    }
}
