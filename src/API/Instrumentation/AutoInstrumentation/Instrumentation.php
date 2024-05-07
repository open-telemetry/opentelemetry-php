<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Instrumentation\AutoInstrumentation;

use OpenTelemetry\Context\ContextStorageInterface;

interface Instrumentation
{
    /**
     * @todo context is nullable in order to support autoloading (and retrieving lazy-loaded tracers), but auto and non-auto
     *       should work the same.
     */
    public function register(HookManager $hookManager, ConfigurationRegistry $configuration, ContextStorageInterface $storage): void;
}
