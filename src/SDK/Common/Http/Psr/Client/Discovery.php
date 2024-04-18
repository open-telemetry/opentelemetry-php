<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Client;

use Generator;
use Http\Discovery\Psr18ClientDiscovery;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\Buzz;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\CurlClient;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\DiscoveryInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\Guzzle;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery\Symfony;
use Psr\Http\Client\ClientInterface;

class Discovery
{
    private static ?array $discoverers;

    /**
     * @var list<class-string<DiscoveryInterface>>
     */
    private const DEFAULTS = [
        Guzzle::class,
        Symfony::class,
        Buzz::class,
        CurlClient::class,
    ];

    /**
     * Attempt discovery of a configurable psr-18 http client, falling back to Psr18ClientDiscovery.
     */
    public static function find(array $options = []): ClientInterface
    {
        $options = array_filter($options);

        foreach (self::discoverers() as $clientDiscovery) {
            /** @var DiscoveryInterface $clientDiscovery */
            if ($clientDiscovery->available()) {
                return $clientDiscovery->create($options);
            }
        }

        return Psr18ClientDiscovery::find();
    }

    /**
     * @internal
     */
    public static function setDiscoverers(array $discoverers): void
    {
        self::$discoverers = $discoverers;
    }

    /**
     * @internal
     */
    public static function reset(): void
    {
        self::$discoverers = null;
    }

    private static function discoverers(): Generator
    {
        foreach (self::$discoverers ?? self::DEFAULTS as $discoverer) {
            if (is_string($discoverer)) {
                yield new $discoverer();
            } else {
                yield $discoverer;
            }
        }
    }
}
