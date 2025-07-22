<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery;

use Buzz\Client\FileGetContents;
use Http\Discovery\Psr17FactoryDiscovery;
use Psr\Http\Client\ClientInterface;

class Buzz implements DiscoveryInterface
{
    /**
     * @phan-suppress PhanUndeclaredClassReference
     */
    #[\Override]
    public function available(): bool
    {
        return class_exists(FileGetContents::class);
    }

    /**
     * @phan-suppress PhanUndeclaredClassReference,PhanTypeMismatchReturn,PhanUndeclaredClassMethod
     */
    #[\Override]
    public function create(mixed $options): ClientInterface
    {
        return new FileGetContents(Psr17FactoryDiscovery::findResponseFactory(), $options);
    }
}
