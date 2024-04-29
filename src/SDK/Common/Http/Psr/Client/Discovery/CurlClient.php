<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery;

use Http\Client\Curl\Client;
use Psr\Http\Client\ClientInterface;

class CurlClient implements DiscoveryInterface
{
    /**
     * @phan-suppress PhanUndeclaredClassReference
     */
    public function available(): bool
    {
        return extension_loaded('curl') && class_exists(Client::class);
    }

    /**
     * @phan-suppress PhanUndeclaredClassReference,PhanTypeMismatchReturn,PhanUndeclaredClassMethod
     * @psalm-suppress UndefinedClass,InvalidReturnType,InvalidReturnStatement
     */
    public function create(mixed $options): ClientInterface
    {
        $options = [
            \CURLOPT_TIMEOUT => $options['timeout'] ?? null,
        ];

        /** @phpstan-ignore-next-line  */
        return new Client(options: array_filter($options));
    }
}
