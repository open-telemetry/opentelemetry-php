<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery;
use PHPUnit\Framework\Attributes\After;

trait TestState
{
    private array $environmentVariables = [];

    #[After]
    protected function tearDownSharedState(): void
    {
        Clock::reset();
        Globals::reset();
        LoggerHolder::unset();
        Logging::reset();
        Discovery::reset();
    }

    #[After]
    protected function restoreEnvironmentVariables(): void
    {
        foreach ($this->environmentVariables as $variable => $value) {
            putenv(false === $value ? $variable : "{$variable}={$value}");
        }
    }

    protected function setEnvironmentVariable(string $variable, mixed $value): void
    {
        if (! isset($this->environmentVariables[$variable])) {
            $this->environmentVariables[$variable] = getenv($variable);
        }

        putenv(null === $value ? $variable : "{$variable}={$value}");
    }
}
