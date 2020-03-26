<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface Attribute
{
    public function getKey(): string;

    /**
     * @return bool|int|float|string|iterable Note: the iterable MUST be homogeneous, i.e. it MUST NOT contain values
     *                                        of different types.
     */
    public function getValue();
}
