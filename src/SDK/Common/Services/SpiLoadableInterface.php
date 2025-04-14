<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Services;

interface SpiLoadableInterface
{
    public function type(): string;
    public function priority(): int;
}
