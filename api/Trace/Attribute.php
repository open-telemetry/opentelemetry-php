<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Attribute
{
    public function getKey(): string;

    /**
     * 
     * @return bool|int|float|string|list<int>|list<float> 
     *
     */
    public function getValue();
}
