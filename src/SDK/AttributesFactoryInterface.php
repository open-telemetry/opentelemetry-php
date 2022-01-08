<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

interface AttributesFactoryInterface
{
    /**
     * @param iterable<non-empty-string, bool|int|float|string|array|null> $attributes
     */
    public function builder(iterable $attributes = []): AttributesBuilderInterface;
}
