<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Export\Http\Discovery;

use GuzzleHttp\Client as Guzzle;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\HttpClient as Symfony;
use Symfony\Component\HttpClient\Psr18Client as SymfonyPsr18;

class ClientDiscovery
{
    public static function find(array $options = []): ClientInterface
    {
        return self::findConfigurableClient(array_filter($options)) ?? Psr18ClientDiscovery::find();
    }

    /**
     * @phan-suppress PhanUndeclaredClassReference
     */
    private static function findConfigurableClient(array $options): ?ClientInterface
    {
        if (class_exists(Guzzle::class)) {
            return self::createGuzzleClient($options);
        }
        if (class_exists(Symfony::class)) {
            return self::createSymfonyClient($options);
        }

        return null;
    }

    /**
     * @phan-suppress PhanUndeclaredClassMethod
     */
    private static function createGuzzleClient(array $options): ClientInterface
    {
        return new Guzzle($options);
    }

    /**
     * @phan-suppress PhanUndeclaredClassMethod,PhanTypeMismatchReturn
     */
    private static function createSymfonyClient(array $options): ClientInterface
    {
        return new SymfonyPsr18(Symfony::create($options));
    }
}
