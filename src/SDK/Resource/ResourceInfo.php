<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource;

use function in_array;
use OpenTelemetry\SDK\Attributes;
use OpenTelemetry\SDK\AttributesInterface;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
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
    use EnvironmentVariablesTrait;

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
        $detectors = (new ResourceInfo(new Attributes()))->getListFromEnvironment(Env::OTEL_PHP_DETECTORS);

        if (in_array('all', $detectors)) {
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
                case 'env':
                    $resourceDetectors[] = new Detectors\Environment();

                    break;
                case 'host':
                    $resourceDetectors[] = new Detectors\Host();

                    break;
                case 'os':
                    $resourceDetectors[] = new Detectors\OperatingSystem();

                    break;
                case 'process':
                    $resourceDetectors[] = new Detectors\Process();

                    break;
                case 'process_runtime':
                    $resourceDetectors[] = new Detectors\ProcessRuntime();

                    break;
                case 'sdk':
                    $resourceDetectors[] = new Detectors\Sdk();

                    break;
                case 'sdk_provided':
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
}
