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
        if (is_array($options)) {
            // Symfony rejects these Guzzle names. Their stream context equivalents are also passed.
            unset($options['verify'], $options['cert'], $options['ssl_key']);
        }
        if (is_array($options) && array_key_exists('timeout', $options)) {
            $options['max_duration'] = $options['timeout'];
            // Symfony applies a zero idle timeout literally. A negative one means no limit.
            if (is_numeric($options['timeout']) && $options['timeout'] <= 0) {
                $options['timeout'] = -1;
            }
        }

        return new Psr18Client(HttpClient::create($options));
    }
}
