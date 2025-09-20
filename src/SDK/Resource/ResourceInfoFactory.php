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
use OpenTelemetry\SemConv\ResourceAttributes;
use RuntimeException;

class ResourceInfoFactory
{
    use LogsMessagesTrait;

    private static ?ResourceInfo $emptyResource = null;

    public static function defaultResource(): ResourceInfo
    {
        $detectors = Configuration::getList(Env::OTEL_PHP_DETECTORS);

        if (in_array(Values::VALUE_ALL, $detectors)) {
            // ascending priority: keys from later detectors will overwrite earlier
            return (new Detectors\Composite([
                new Detectors\Host(),
                new Detectors\Process(),
                ...Registry::resourceDetectors(),
                new Detectors\Environment(),  // OTEL_RESOURCE_ATTRIBUTES
                new Detectors\Sdk(),
                new Detectors\Service(),      // OTEL_SERVICE_NAME overrides OTEL_RESOURCE_ATTRIBUTES
                new Detectors\Apache(),       // Override Service UUID with stable ID for Apache
                new Detectors\Fpm(),          // Override Service UUID with stable ID for FPM
                new Detectors\Kubernetes(),   // Override Service UUID with stable ID for K8s
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
                case Values::VALUE_DETECTORS_PROCESS:
                    $resourceDetectors[] = new Detectors\Process();

                    break;

                case Values::VALUE_DETECTORS_COMPOSER:
                    $resourceDetectors[] = new Detectors\Composer();

                    break;
                case Values::VALUE_DETECTORS_APACHE:
                    $resourceDetectors[] = new Detectors\Apache();

                    break;
                case Values::VALUE_DETECTORS_FPM:
                    $resourceDetectors[] = new Detectors\Fpm();

                    break;
                case Values::VALUE_DETECTORS_KUBERNETES:
                    $resourceDetectors[] = new Detectors\Kubernetes();

                    break;
                case Values::VALUE_DETECTORS_SDK_PROVIDED: //deprecated
                case Values::VALUE_DETECTORS_OS: //deprecated
                case Values::VALUE_DETECTORS_PROCESS_RUNTIME: //deprecated
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
        $resourceDetectors [] = new Detectors\Sdk();
        $resourceDetectors [] = new Detectors\Service();
        $resourceDetectors [] = new Detectors\Apache();     // Override Service UUID with stable ID for Apache
        $resourceDetectors [] = new Detectors\Fpm();        // Override Service UUID with stable ID for FPM

        return (new Detectors\Composite($resourceDetectors))->getResource();
    }

    public static function emptyResource(): ResourceInfo
    {
        if (null === self::$emptyResource) {
            self::$emptyResource = ResourceInfo::create(Attributes::create([]));
        }

        return self::$emptyResource;
    }

    public static function mandatoryResource(): ResourceInfo
    {
        return ResourceInfo::create(
            Attributes::create(
                [
                    ResourceAttributes::SERVICE_NAME => 'unknown_service:php',
                ],
            )
        );
    }
}
