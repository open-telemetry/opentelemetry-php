<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Environment;

use function assert;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Config\ResourceCheckerInterface;

final class EnvResourceChecker implements ResourceCheckerInterface
{
    public function __construct(
        private readonly EnvReader $envReader,
    ) {
    }

    public function supports(ResourceInterface $metadata): bool
    {
        return $metadata instanceof EnvResource;
    }

    public function isFresh(ResourceInterface $resource, int $timestamp): bool
    {
        assert($resource instanceof EnvResource);

        return $this->envReader->read($resource->name) === $resource->value;
    }
}
