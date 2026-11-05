<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace\AutoRootSpan;

use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Context as InstrumentationContext;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\HookManagerInterface;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\Instrumentation;
use OpenTelemetry\Context\Context;

final class AutoRootSpanInstrumentation implements Instrumentation
{
    #[\Override]
    public function register(HookManagerInterface $hookManager, ConfigProperties $configuration, InstrumentationContext $context): void
    {
        /**
         * Feature entrypoint, previously {@see \OpenTelemetry\SDK\Trace\AutoRootSpan::start()}
         */
    }
}
