<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource;

use OpenTelemetry\API\AttributesInterface;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SemConv\ResourceAttributes;

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

    private function __construct(AttributesInterface $attributes)
    {
        $this->attributes = $attributes;
    }

    public static function create(AttributesInterface $attributes): self
    {
        /*
         * The SDK MUST extract information from the OTEL_RESOURCE_ATTRIBUTES environment
         * variable and merge this.
         */
        $resource = self::merge(
            self::defaultResource(),
            self::merge(
                new ResourceInfo(clone $attributes),
                self::environmentResource()
            ),
        );

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
            $mergedAttribute = $mergedAttributes->get($name);
            if (null === $mergedAttribute || $mergedAttribute === '') {
                $mergedAttributes->setAttribute($name, $attribute);
            }
        }

        return new ResourceInfo($mergedAttributes);
    }

    public static function defaultResource(): self
    {
        return new ResourceInfo(new Attributes(
            [
                ResourceAttributes::TELEMETRY_SDK_NAME => 'opentelemetry',
                ResourceAttributes::TELEMETRY_SDK_LANGUAGE => 'php',
                ResourceAttributes::TELEMETRY_SDK_VERSION => 'dev',
            ]
        ));
    }

    /**
     * Create resource attributes from OTEL_RESOURCE_ATTRIBUTES key=value comma-separated entries
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/resource/sdk.md#specifying-resource-information-via-an-environment-variable
     */
    public static function environmentResource(): self
    {
        $attributes = [
            ResourceAttributes::SERVICE_NAME => 'unknown_service',
        ];
        $string = getenv('OTEL_RESOURCE_ATTRIBUTES');
        if ($string && false !== strpos($string, '=')) {
            foreach (explode(',', $string) as $pair) {
                [$key, $value] = explode('=', $pair);
                $attributes[trim($key)] = trim($value);
            }
        }
        //@see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/sdk-environment-variables.md#general-sdk-configuration
        $serviceName = getenv('OTEL_SERVICE_NAME');
        if ($serviceName) {
            $attributes[ResourceAttributes::SERVICE_NAME] = $serviceName;
        }

        return new ResourceInfo(new Attributes($attributes));
    }

    public static function emptyResource(): self
    {
        return new ResourceInfo(new Attributes());
    }

    public function getAttributes(): AttributesInterface
    {
        return $this->attributes;
    }
}
