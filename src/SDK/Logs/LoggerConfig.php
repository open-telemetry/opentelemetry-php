<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Common\InstrumentationScope\State;

class LoggerConfig
{
    public function __construct(private readonly State $state = State::ENABLED)
    {
    }

    public function isEnabled(): bool
    {
        return $this->state === State::ENABLED;
    }

    public static function default(): self
    {
        return new self();
    }
}
