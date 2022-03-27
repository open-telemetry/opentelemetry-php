<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource;

use function in_array;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;
use OpenTelemetry\SDK\Common\Environment\Accessor;
use OpenTelemetry\SDK\Common\Environment\KnownValues as Values;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;

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
        $detectors = Accessor::getList(Env::OTEL_PHP_DETECTORS);

        if (in_array(Values::VALUE_ALL, $detectors)) {
            return (new Detectors\Composite([
                new Detectors\Environment(),
                new Detectors\Host(),
                new Detectors\OperatingSystem(),
                new Detectors\Process(),
                new Detectors\ProcessRuntime(),
                new Detectors\Sdk(),
                new Detectors\SdkProvided(),
            ]))->getResource();
        }

        $resourceDetectors = [];

        foreach ($detectors as $detector) {
            switch ($detector) {
                case Values::VALUE_DETECTORS_ENVIRONMENT:
                    $resourceDetectors[] = new Detectors\Environment();

                    break;
                case Values::VALUE_DETECTORS_HOST:
                    $resourceDetectors[] = new Detectors\Host();

                    break;
                case Values::VALUE_DETECTORS_OS:
                    $resourceDetectors[] = new Detectors\OperatingSystem();

                    break;
                case Values::VALUE_DETECTORS_PROCESS:
                    $resourceDetectors[] = new Detectors\Process();

                    break;
                case Values::VALUE_DETECTORS_PROCESS_RUNTIME:
                    $resourceDetectors[] = new Detectors\ProcessRuntime();

                    break;
                case Values::VALUE_DETECTORS_SDK:
                    $resourceDetectors[] = new Detectors\Sdk();

                    break;
                case Values::VALUE_DETECTORS_SDK_PROVIDED:
                    $resourceDetectors[] = new Detectors\SdkProvided();

                    break;
                default:
            }
        }

        return (new Detectors\Composite($resourceDetectors))->getResource();
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

    //TODO - figure out how to ensure this doesn't get out of sync as new properties are added (a test using reflection perhaps?)
    public function serialize(): string
    {
        $copyOfAttributesAsArray = [...$this->attributes->toArray()];
        ksort($copyOfAttributesAsArray); //sort the associative array by keys since the serializer will consider equal arrays different otherwise

        $dehydratedAsArray = [
            'schemaUrl' => $this->schemaUrl,
            'attributes' => $copyOfAttributesAsArray
        ];

        $serializedAsString = serialize($dehydratedAsArray);

        return $serializedAsString;
    }
}
