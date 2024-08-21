<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

interface Instrumentation
{
    public function register(HookManagerInterface $hookManager, ConfigurationRegistry $configuration, Context $context): void;
}
