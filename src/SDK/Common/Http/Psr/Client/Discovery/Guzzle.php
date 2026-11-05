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
        if (is_array($options)) {
            // By default Guzzle decodes the response body before the transport sees it, without a limit on its
            // size, and renames the Content-Encoding header.
            $options += ['decode_content' => false];
        }

        return new Client($options);
    }
}
