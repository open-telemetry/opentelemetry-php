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
    private ?RequestFactoryInterface $requestFactory;
    private ?ResponseFactoryInterface $responseFactory;
    private ?ServerRequestFactoryInterface $serverRequestFactory;
    private ?StreamFactoryInterface $streamFactory;
    private ?UploadedFileFactoryInterface $uploadedFileFactory;
    private ?UriFactoryInterface $uriFactory;

    public function __construct(
        ?RequestFactoryInterface $requestFactory = null,
        ?ResponseFactoryInterface $responseFactory = null,
        ?ServerRequestFactoryInterface $serverRequestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?UploadedFileFactoryInterface $uploadedFileFactory = null,
        ?UriFactoryInterface $uriFactory = null
    ) {
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->serverRequestFactory = $serverRequestFactory;
        $this->streamFactory = $streamFactory;
        $this->uploadedFileFactory = $uploadedFileFactory;
        $this->uriFactory = $uriFactory;
    }

    public static function create(
        ?RequestFactoryInterface $requestFactory = null,
        ?ResponseFactoryInterface $responseFactory = null,
        ?ServerRequestFactoryInterface $serverRequestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?UploadedFileFactoryInterface $uploadedFileFactory = null,
        ?UriFactoryInterface $uriFactory = null
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

    public function resolveRequestFactory(): RequestFactoryInterface
    {
        return $this->requestFactory ??= Psr17FactoryDiscovery::findRequestFactory();
    }

    public function resolveResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory ??= Psr17FactoryDiscovery::findResponseFactory();
    }

    public function resolveServerRequestFactory(): ServerRequestFactoryInterface
    {
        return $this->serverRequestFactory ??= Psr17FactoryDiscovery::findServerRequestFactory();
    }

    public function resolveStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory ??= Psr17FactoryDiscovery::findStreamFactory();
    }

    public function resolveUploadedFileFactory(): UploadedFileFactoryInterface
    {
        return $this->uploadedFileFactory ??= Psr17FactoryDiscovery::findUploadedFileFactory();
    }

    public function resolveUriFactory(): UriFactoryInterface
    {
        return $this->uriFactory ??= Psr17FactoryDiscovery::findUriFactory();
    }
}
