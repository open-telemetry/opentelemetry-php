<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource;

use function in_array;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues as Values;
use OpenTelemetry\SDK\Common\Configuration\Variables as Env;
use OpenTelemetry\SDK\Registry;
use RuntimeException;

class ResourceInfoFactory
{
    use LogsMessagesTrait;

    public static function defaultResource(): ResourceInfo
    {
        $detectors = Configuration::getList(Env::OTEL_PHP_DETECTORS);

        if (in_array(Values::VALUE_ALL, $detectors)) {
            // ascending priority: keys from later detectors will overwrite earlier
            return (new Detectors\Composite([
                new Detectors\Host(),
                new Detectors\OperatingSystem(),
                new Detectors\Process(),
                new Detectors\ProcessRuntime(),
                new Detectors\Sdk(),
                new Detectors\SdkProvided(),
                new Detectors\Composer(),
                ...Registry::resourceDetectors(),
                new Detectors\Environment(),
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

                case Values::VALUE_DETECTORS_COMPOSER:
                    $resourceDetectors[] = new Detectors\Composer();

                    break;
                case Values::VALUE_NONE:

                    break;
                default:
                    try {
                        $resourceDetectors[] = Registry::resourceDetector($detector);
                    } catch (RuntimeException $e) {
                        self::logWarning($e->getMessage());
                    }
            }
        }

        return (new Detectors\Composite($resourceDetectors))->getResource();
    }

    public static function emptyResource(): ResourceInfo
    {
        return ResourceInfo::create(Attributes::create([]));
    }
}
