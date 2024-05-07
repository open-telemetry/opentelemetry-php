<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

use OpenTelemetry\Context\ContextStorageInterface;

interface Instrumentation
{
    public function register(HookManager $hookManager, ConfigurationRegistry $configuration, ContextStorageInterface $storage): void;
}