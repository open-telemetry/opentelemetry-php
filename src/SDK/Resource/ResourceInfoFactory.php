<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource;

use function in_array;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues as Values;
use OpenTelemetry\SDK\Common\Configuration\Variables as Env;

class ResourceInfoFactory
{
    /**
     * Merges resources into a new one.
     *
     * @param ResourceInfo ...$resources
     * @return ResourceInfo
     */
    public static function merge(ResourceInfo ...$resources): ResourceInfo
    {
        $attributes = [];

        foreach ($resources as $resource) {
            $attributes += $resource->getAttributes()->toArray();
        }

        $schemaUrl = self::mergeSchemaUrl(...$resources);

        return ResourceInfo::create(Attributes::create($attributes), $schemaUrl);
    }

    public static function defaultResource(): ResourceInfo
    {
        $detectors = Configuration::getList(Env::OTEL_PHP_DETECTORS);

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
        return ResourceInfo::create(Attributes::create([]));
    }

    private static function mergeSchemaUrl(ResourceInfo ...$resources): ?string
    {
        $schemaUrl = null;
        foreach ($resources as $resource) {
            if ($schemaUrl !== null && $resource->getSchemaUrl() !== null && $schemaUrl !== $resource->getSchemaUrl()) {
                // stop the merging if non-empty conflicting schemas are detected
                return null;
            }
            $schemaUrl ??= $resource->getSchemaUrl();
        }

        return $schemaUrl;
    }
}
