<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests;

use OpenTelemetry\API\Behavior\Internal\Logging;
use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Globals;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\SDK\Common\Export\InMemoryStorageManager;
use OpenTelemetry\SDK\Common\Http\Psr\Client\Discovery;
use PHPUnit\Framework\Attributes\After;

trait TestState
{
    private array $environmentVariables = [];
    private array $serverVariables = [];

    #[After]
    protected function tearDownSharedState(): void
    {
        Clock::reset();
        Globals::reset();
        LoggerHolder::unset();
        Logging::reset();
        Discovery::reset();
        InMemoryStorageManager::reset();
    }

    #[After]
    protected function restoreEnvironmentVariables(): void
    {
        foreach ($this->environmentVariables as $variable => $value) {
            putenv(false === $value ? $variable : "{$variable}={$value}");
        }
        foreach ($this->serverVariables as $variable => $value) {
            if ($value === null) {
                unset($_SERVER[$variable]);
            } else {
                $_SERVER[$variable] = $value;
            }
        }
    }

    /**
     * A real environment variable is visible through both `getenv()` and `$_SERVER`, so both are
     * set here: `putenv()` alone is invisible to anything reading `$_SERVER`, such as
     * {@see \OpenTelemetry\Config\SDK\Configuration\Environment\ServerEnvSource}.
     *
     * @psalm-suppress InvalidArrayOffset
     */
    protected function setEnvironmentVariable(string $variable, mixed $value): void
    {
        if (! isset($this->environmentVariables[$variable])) {
            $this->environmentVariables[$variable] = getenv($variable);
        }
        if (! array_key_exists($variable, $this->serverVariables)) {
            $this->serverVariables[$variable] = $_SERVER[$variable] ?? null;
        }

        putenv(null === $value ? $variable : "{$variable}={$value}");
        if (null === $value) {
            unset($_SERVER[$variable]);
        } else {
            $_SERVER[$variable] = $value;
        }
    }
}
