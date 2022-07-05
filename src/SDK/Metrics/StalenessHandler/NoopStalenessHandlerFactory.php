<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use OpenTelemetry\SDK\Metrics\ReferenceCounter;
use OpenTelemetry\SDK\Metrics\StalenessHandler;
use OpenTelemetry\SDK\Metrics\StalenessHandlerFactory;

final class NoopStalenessHandlerFactory implements StalenessHandlerFactory {

    public function create(): StalenessHandler&ReferenceCounter {
        static $instance = new NoopStalenessHandler();
        return $instance;
    }
}
