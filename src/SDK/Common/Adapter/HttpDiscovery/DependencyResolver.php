<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Adapter\HttpDiscovery;

use Http\Client\HttpAsyncClient;
use OpenTelemetry\SDK\Common\Http\DependencyResolverInterface;
use OpenTelemetry\SDK\Common\Http\HttpPlug\Client\ResolverInterface as HttpPlugClientResolverInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Client\ResolverInterface as PsrClientResolverInterface;
use OpenTelemetry\SDK\Common\Http\Psr\Message\FactoryResolverInterface as MessageFactoryResolverInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

final class DependencyResolver implements DependencyResolverInterface
{
    private readonly MessageFactoryResolverInterface $messageFactoryResolver;
    private readonly PsrClientResolverInterface $psrClientResolver;
    private readonly HttpPlugClientResolverInterface $httpPlugClientResolver;

    public function __construct(
        ?MessageFactoryResolverInterface $messageFactoryResolver = null,
        ?PsrClientResolverInterface $psrClientResolver = null,
        ?HttpPlugClientResolverInterface $httpPlugClientResolver = null,
    ) {
        $this->messageFactoryResolver = $messageFactoryResolver ?? MessageFactoryResolver::create();
        $this->psrClientResolver = $psrClientResolver ?? PsrClientResolver::create();
        $this->httpPlugClientResolver = $httpPlugClientResolver ?? HttpPlugClientResolver::create();
    }

    public static function create(
        ?MessageFactoryResolverInterface $messageFactoryResolver = null,
        ?PsrClientResolverInterface $psrClientResolver = null,
        ?HttpPlugClientResolverInterface $httpPlugClientResolver = null,
    ): self {
        return new self($messageFactoryResolver, $psrClientResolver, $httpPlugClientResolver);
    }

    public function resolveRequestFactory(): RequestFactoryInterface
    {
        return $this->messageFactoryResolver->resolveRequestFactory();
    }

    public function resolveResponseFactory(): ResponseFactoryInterface
    {
        return $this->messageFactoryResolver->resolveResponseFactory();
    }

    public function resolveServerRequestFactory(): ServerRequestFactoryInterface
    {
        return $this->messageFactoryResolver->resolveServerRequestFactory();
    }

    public function resolveStreamFactory(): StreamFactoryInterface
    {
        return $this->messageFactoryResolver->resolveStreamFactory();
    }

    public function resolveUploadedFileFactory(): UploadedFileFactoryInterface
    {
        return $this->messageFactoryResolver->resolveUploadedFileFactory();
    }

    public function resolveUriFactory(): UriFactoryInterface
    {
        return $this->messageFactoryResolver->resolveUriFactory();
    }

    public function resolveHttpPlugAsyncClient(): HttpAsyncClient
    {
        return $this->httpPlugClientResolver->resolveHttpPlugAsyncClient();
    }

    public function resolvePsrClient(): ClientInterface
    {
        return $this->psrClientResolver->resolvePsrClient();
    }
}
