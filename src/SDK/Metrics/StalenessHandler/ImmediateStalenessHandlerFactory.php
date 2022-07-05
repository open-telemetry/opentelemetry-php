<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\StalenessHandler;

use OpenTelemetry\SDK\Metrics\ReferenceCounter;
use OpenTelemetry\SDK\Metrics\StalenessHandler;
use OpenTelemetry\SDK\Metrics\StalenessHandlerFactory;

final class ImmediateStalenessHandlerFactory implements StalenessHandlerFactory {

    public function create(): StalenessHandler&ReferenceCounter {
        return new ImmediateStalenessHandler();
    }
}
