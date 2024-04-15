<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration;

use Symfony\Component\Config\Resource\ClassExistenceResource;
use Symfony\Component\Config\Resource\ReflectionClassResource;
use Symfony\Component\Config\Resource\ResourceInterface;

interface ResourceCollection
{

    /**
     * @param object|class-string $class
     *
     * @see ClassExistenceResource
     * @see ReflectionClassResource
     */
    public function addClassResource(object|string $class): void;

    public function addResource(ResourceInterface $resource): void;
}
