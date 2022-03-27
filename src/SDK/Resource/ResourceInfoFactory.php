<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource;

use function in_array;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Environment\Accessor;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariablesTrait;
use OpenTelemetry\SDK\Common\Environment\KnownValues as Values;
use OpenTelemetry\SDK\Common\Environment\Variables as Env;

class ResourceInfoFactory
{
    use EnvironmentVariablesTrait;

    /**
     * Merges resources into a new one.
     *
     * @param ResourceInfo ...$resources
     * @return ResourceInfo
     */
    public static function merge(ResourceInfo ...$resources): ResourceInfo
    {
        $attributes = [];
        $schemaUrl = null;

        foreach ($resources as $resource) {
            $attributes += $resource->getAttributes()->toArray();
            $schemaUrl ??= $resource->getSchemaUrl();
        }

        return ResourceInfo::create(new Attributes($attributes), $schemaUrl);
    }

    public static function defaultResource(): ResourceInfo
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

    public static function emptyResource(): ResourceInfo
    {
        return ResourceInfo::create(new Attributes());
    }
}
