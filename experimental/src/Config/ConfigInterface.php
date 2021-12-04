<?php

declare(strict_types=1);

namespace OpenTelemetry\Experimental\Config;

interface ConfigInterface
{
    public function __construct(array $userConfig, array $environmentConfig);
}