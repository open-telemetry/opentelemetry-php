<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK\Common\Configuration;

use OpenTelemetry\Config\SDK\Configuration\Environment\ArrayEnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSource;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvSourceProvider;

class TestEnvSourceProvider implements EnvSourceProvider
{
    #[\Override]
    public function getEnvSource(): EnvSource
    {
        return new ArrayEnvSource([
            'CONFIG_SOURCE' => 'from-test',
            'CONFIG_SOURCE_TEST_ONLY' => 'from-test',
        ]);
    }
}
