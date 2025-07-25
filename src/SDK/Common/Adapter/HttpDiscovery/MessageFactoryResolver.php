<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Adapter\HttpDiscovery;

use Http\Discovery\Psr17FactoryDiscovery;
use OpenTelemetry\SDK\Common\Http\Psr\Message\FactoryResolverInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

final class MessageFactoryResolver implements FactoryResolverInterface
{
    public function __construct(
        private ?RequestFactoryInterface $requestFactory = null,
        private ?ResponseFactoryInterface $responseFactory = null,
        private ?ServerRequestFactoryInterface $serverRequestFactory = null,
        private ?StreamFactoryInterface $streamFactory = null,
        private ?UploadedFileFactoryInterface $uploadedFileFactory = null,
        private ?UriFactoryInterface $uriFactory = null,
    ) {
    }

    public static function create(
        ?RequestFactoryInterface $requestFactory = null,
        ?ResponseFactoryInterface $responseFactory = null,
        ?ServerRequestFactoryInterface $serverRequestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?UploadedFileFactoryInterface $uploadedFileFactory = null,
        ?UriFactoryInterface $uriFactory = null,
    ): self {
        return new self(
            $requestFactory,
            $responseFactory,
            $serverRequestFactory,
            $streamFactory,
            $uploadedFileFactory,
            $uriFactory
        );
    }

    #[\Override]
    public function resolveRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory ??= Psr17FactoryDiscovery::findRequestFactory();
    }

    #[\Override]
    public function resolveResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory ??= Psr17FactoryDiscovery::findResponseFactory();
    }

    #[\Override]
    public function resolveServerRequestFactory(): ServerRequestFactoryInterface
    {
        return $this->serverRequestFactory ??= Psr17FactoryDiscovery::findServerRequestFactory();
    }

    #[\Override]
    public function resolveStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory ??= Psr17FactoryDiscovery::findStreamFactory();
    }

    #[\Override]
    public function resolveUploadedFileFactory(): UploadedFileFactoryInterface
    {
        return $this->uploadedFileFactory ??= Psr17FactoryDiscovery::findUploadedFileFactory();
    }

    #[\Override]
    public function resolveUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory ??= Psr17FactoryDiscovery::findUriFactory();
    }
}
