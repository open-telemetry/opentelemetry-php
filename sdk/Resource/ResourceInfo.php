<?php

declare(strict_types=1);

namespace OpenTelemetry\Sdk\Resource;

use OpenTelemetry\Sdk\Trace\Attributes;

/**
 * A Resource is an immutable representation of the entity producing telemetry. For example, a process producing telemetry
 * that is running in a container on Kubernetes has a Pod name, it is in a namespace and possibly is part of a Deployment
 * which also has a name. All three of these attributes can be included in the Resource.
 *
 * The class named as ResourceInfo due to `resource` is the soft reserved word in PHP.
 */
class ResourceInfo
{
    private $attributes;

    private function __construct(Attributes $attributes)
    {
        $this->attributes = $attributes;
    }

    public static function create(Attributes $attributes): self
    {
        $resource = self::merge(self::defaultResource(), new ResourceInfo(clone $attributes));
        /*
         * The SDK MUST extract information from the OTEL_RESOURCE_ATTRIBUTES environment
         * variable and merge this.
         * todo: after resource detection is implemented, merge it here.
         * return $resource.merge(....);
         *
         */
        return $resource;
    }

    /**
     * Merges two resources into a new one.
     * Conflicts (i.e. a key for which attributes exist on both the primary and secondary resource) are handled as follows:
     * - If the value on the primary resource is an empty string, the result has the value of the secondary resource.
     * - Otherwise, the value of the primary resource is used.
     *
     * @param ResourceInfo $primary
     * @param ResourceInfo $secondary
     * @return ResourceInfo
     */
    public static function merge(ResourceInfo $primary, ResourceInfo $secondary): self
    {
        // clone attributes from the primary resource
        $mergedAttributes = clone $primary->getAttributes();

        // merge attributes from the secondary resource
        foreach ($secondary->getAttributes() as $name => $attribute) {
            $mergedAttribute = $mergedAttributes->getAttribute($name);
            if (null === $mergedAttribute || $mergedAttribute->getValue() === '') {
                $mergedAttributes->setAttribute($name, $attribute->getValue());
            }
        }

        return new ResourceInfo($mergedAttributes);
    }

    public static function defaultResource(): self
    {
        return new ResourceInfo(new Attributes(
            [
                ResourceConstants::TELEMETRY_SDK_NAME => 'opentelemetry',
                ResourceConstants::TELEMETRY_SDK_LANGUAGE => 'php',
                ResourceConstants::TELEMETRY_SDK_VERSION => 'dev',
            ]
        ));
    }

    public static function emptyResource(): self
    {
        return new ResourceInfo(new Attributes());
    }

    public function getAttributes(): Attributes
    {
        return $this->attributes;
    }
}
