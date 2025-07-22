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
    #[\Override]
    public function available(): bool
    {
        return class_exists(HttpClient::class) && class_exists(Psr18Client::class);
    }

    /**
     * @phan-suppress PhanTypeMismatchReturn,PhanUndeclaredClassMethod
     */
    #[\Override]
    public function create(mixed $options): ClientInterface
    {
        if (is_array($options) && array_key_exists('timeout', $options)) {
            $options['max_duration'] = $options['timeout'];
        }

        return new Psr18Client(HttpClient::create($options));
    }
}
