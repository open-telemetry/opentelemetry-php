<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource;

use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesInterface;

/**
 * A Resource is an immutable representation of the entity producing telemetry. For example, a process producing telemetry
 * that is running in a container on Kubernetes has a Pod name, it is in a namespace and possibly is part of a Deployment
 * which also has a name. All three of these attributes can be included in the Resource.
 *
 * The class named as ResourceInfo due to `resource` is the soft reserved word in PHP.
 */
class ResourceInfo
{
    private AttributesInterface $attributes;
    private ?string $schemaUrl;

    private function __construct(AttributesInterface $attributes, ?string $schemaUrl = null)
    {
        $this->attributes = $attributes;
        $this->schemaUrl = $schemaUrl;
    }

    public static function create(AttributesInterface $attributes, ?string $schemaUrl = null): self
    {
        return new ResourceInfo(clone $attributes, $schemaUrl);
    }

    /**
     * Merges resources into a new one.
     *
     * @param ResourceInfo ...$resources
     * @return ResourceInfo
     */
    public static function merge(ResourceInfo ...$resources): self
    {
        $attributes = [];
        $schemaUrl = null;

        foreach ($resources as $resource) {
            $attributes += $resource->getAttributes()->toArray();
            $schemaUrl ??= $resource->getSchemaUrl();
        }

        return new ResourceInfo(new Attributes($attributes), $schemaUrl);
    }

    public static function defaultResource(): self
    {
        return (new Detectors\Composite([
            new Detectors\Env(),

            new Detectors\Host(),
            new Detectors\Os(),
            new Detectors\Process(),
            new Detectors\ProcessRuntime(),

            new Detectors\Sdk(),
            new Detectors\SdkProvided(),
        ]))->getResource();
    }

    public static function emptyResource(): self
    {
        return new ResourceInfo(new Attributes());
    }

    public function getAttributes(): AttributesInterface
    {
        return $this->attributes;
    }

    public function getSchemaUrl(): ?string
    {
        return $this->schemaUrl;
    }
}
