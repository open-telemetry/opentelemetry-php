<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration\ConfigEnv;

use OpenTelemetry\API\Configuration\Context;

/**
 * @template T
 */
interface EnvComponentLoader
{
    /**
     * @psalm-return T
     */
    public function load(EnvResolver $env, EnvComponentLoaderRegistry $registry, Context $context): mixed;

    public function name(): string;
}
