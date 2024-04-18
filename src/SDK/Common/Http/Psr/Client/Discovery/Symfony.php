<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery;

use Psr\Http\Client\ClientInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class Symfony implements DiscoveryInterface
{
    /**
     * @phan-suppress PhanUndeclaredClassReference
     */
    public function available(): bool
    {
        return class_exists(HttpClient::class) && class_exists(Psr18Client::class);
    }

    /**
     * @phan-suppress PhanTypeMismatchReturn,PhanUndeclaredClassMethod
     */
    public function create(mixed $options): ClientInterface
    {
        return new Psr18Client(HttpClient::create($options));
    }
}
