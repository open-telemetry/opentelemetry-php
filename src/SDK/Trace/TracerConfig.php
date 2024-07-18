<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\InstrumentationScope\State;

/**
 * @internal
 */
class TracerConfig
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
