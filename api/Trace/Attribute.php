<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Attribute
{
    public function getKey(): string;

    /**
     *
     * @return bool|int|float|string|list<bool>|list<int>|list<float>|list<string>
     *
     */
    public function getValue();
}
