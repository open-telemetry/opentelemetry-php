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
                new Detectors\Environment(),
                new Detectors\Sdk(),
                new Detectors\Service(),
                ...Registry::resourceDetectors(),
            ]))->getResource();
        }

        /**
         * Process env-provided detectors:
         * - host, process, environment, composer first if requested
         * - sdk, service always
         * - any other detectors registered in the registry if requested
         */
        $resourceDetectors = [];
        foreach ([
            Values::VALUE_DETECTORS_HOST => Detectors\Host::class,
            Values::VALUE_DETECTORS_PROCESS => Detectors\Process::class,
            Values::VALUE_DETECTORS_ENVIRONMENT => Detectors\Environment::class,
            Values::VALUE_DETECTORS_COMPOSER => Detectors\Composer::class,
            Values::VALUE_DETECTORS_SERVICE_INSTANCE => Detectors\ServiceInstance::class,
        ] as $detector => $class) {
            if (in_array($detector, $detectors)) {
                $resourceDetectors[] = new $class();
                $detectors = array_diff($detectors, [$detector]);
            }
        }
        $resourceDetectors [] = new Detectors\Sdk();
        $resourceDetectors [] = new Detectors\Service();
        // Don't try to load mandatory + deprecated detectors
        $detectors = array_diff($detectors, [
            Values::VALUE_DETECTORS_SDK,
            Values::VALUE_DETECTORS_SERVICE,
            Values::VALUE_DETECTORS_SDK_PROVIDED, //deprecated
            Values::VALUE_DETECTORS_OS, //deprecated
            Values::VALUE_DETECTORS_PROCESS_RUNTIME, //deprecated
            Values::VALUE_NONE,
        ]);

        foreach ($detectors as $detector) {
            try {
                $resourceDetectors[] = Registry::resourceDetector($detector);
            } catch (RuntimeException $e) {
                self::logWarning($e->getMessage());
            }
        }

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
