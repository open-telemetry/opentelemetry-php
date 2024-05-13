<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

interface Instrumentation
{
    public function register(HookManager $hookManager, ConfigurationRegistry $configuration): void;
}
