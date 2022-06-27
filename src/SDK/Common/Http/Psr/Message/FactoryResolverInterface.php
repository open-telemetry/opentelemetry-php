<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Http\Psr\Message;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

interface FactoryResolverInterface
{
    public function resolveRequestFactory(): RequestFactoryInterface;
    public function resolveResponseFactory(): ResponseFactoryInterface;
    public function resolveServerRequestFactory(): ServerRequestFactoryInterface;
    public function resolveStreamFactory(): StreamFactoryInterface;
    public function resolveUploadedFileFactory(): UploadedFileFactoryInterface;
    public function resolveUriFactory(): UriFactoryInterface;
}
