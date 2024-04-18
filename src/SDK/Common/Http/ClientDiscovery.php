<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http;

use Http\Discovery\Psr18ClientDiscovery;
use OpenTelemetry\SDK\Common\Http\Discovery\DiscoveryInterface;
use OpenTelemetry\SDK\Common\Http\Discovery\Guzzle;
use OpenTelemetry\SDK\Common\Http\Discovery\Symfony;
use Psr\Http\Client\ClientInterface;

class ClientDiscovery
{
    /**
     * @var list<class-string<DiscoveryInterface>>
     */
    private const KNOWN_CLIENTS = [
        Guzzle::class,
        Symfony::class,
    ];

    /**
     * Attempt discovery of a configurable psr-18 http client, falling back to Psr18ClientDiscovery.
     */
    public static function find(array $options = []): ClientInterface
    {
        $options = array_filter($options);

        foreach (self::KNOWN_CLIENTS as $class) {
            /** @var DiscoveryInterface $clientDiscovery */
            $clientDiscovery = new $class();
            if ($clientDiscovery->available()) {
                return $clientDiscovery->create($options);
            }
        }

        return Psr18ClientDiscovery::find();
    }
}
