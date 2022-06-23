<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

use OpenTelemetry\SDK\Common\Dev\Compatibility\Util as BcUtil;

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
        return new ResourceInfo($attributes, $schemaUrl);
    }

    public function getAttributes(): AttributesInterface
    {
        return $this->attributes;
    }

    public function getSchemaUrl(): ?string
    {
        return $this->schemaUrl;
    }

    public function serialize(): string
    {
        $copyOfAttributesAsArray = array_slice($this->attributes->toArray(), 0); //This may be overly cautious (in trying to avoid mutating the source array)
        ksort($copyOfAttributesAsArray); //sort the associative array by keys since the serializer will consider equal arrays different otherwise

        //The exact return value doesn't matter, as long as it can distingusih between instances that represent the same/different resources
        return serialize([
            'schemaUrl' => $this->schemaUrl,
            'attributes' => $copyOfAttributesAsArray,
        ]);
    }

    /**
     * Backward compatibility methods
     *
     * @codeCoverageIgnore
     */
    public static function merge(ResourceInfo ...$resources): ResourceInfo
    {
        BcUtil::triggerMethodDeprecationNotice(
            __METHOD__,
            'merge',
            ResourceInfoFactory::class
        );

        return ResourceInfoFactory::merge(...$resources);
    }

    /**
     * @codeCoverageIgnore
     */
    public static function defaultResource(): ResourceInfo
    {
        BcUtil::triggerMethodDeprecationNotice(
            __METHOD__,
            'defaultResource',
            ResourceInfoFactory::class
        );

        return ResourceInfoFactory::defaultResource();
    }

    /**
     * @codeCoverageIgnore
     */
    public static function emptyResource(): ResourceInfo
    {
        BcUtil::triggerMethodDeprecationNotice(
            __METHOD__,
            'emptyResource',
            ResourceInfoFactory::class
        );

        return ResourceInfoFactory::emptyResource();
    }
}
