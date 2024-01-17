<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
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
    use LogsMessagesTrait;

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

        //The exact return value doesn't matter, as long as it can distinguish between instances that represent the same/different resources
        return serialize([
            'schemaUrl' => $this->schemaUrl,
            'attributes' => $copyOfAttributesAsArray,
        ]);
    }

    /**
     * Merge current resource with an updating resource, combining all attributes. If a key exists on both the old and updating
     * resource, the value of the updating resource MUST be picked (even if the updated value is empty)
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.20.0/specification/resource/sdk.md#merge
     * @todo can we optimize this to avoid re-validating the attributes on merge?
     */
    public function merge(ResourceInfo $updating): ResourceInfo
    {
        $schemaUrl = self::mergeSchemaUrl($this->getSchemaUrl(), $updating->getSchemaUrl());
        $attributes = Attributes::factory()->builder()->merge($this->getAttributes(), $updating->getAttributes());

        return ResourceInfo::create($attributes, $schemaUrl);
    }

    /**
     * Merge the schema URLs from the old and updating resource.
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.20.0/specification/resource/sdk.md#merge
     */
    private static function mergeSchemaUrl(?string $old, ?string $updating): ?string
    {
        if (empty($old)) {
            return $updating;
        }
        if (empty($updating)) {
            return $old;
        }
        if ($old === $updating) {
            return $old;
        }

        self::logWarning('Merging resources with different schema URLs', [
            'old' => $old,
            'updating' => $updating,
        ]);

        return null;
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
