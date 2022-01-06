<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

interface AttributesFactoryInterface
{
    public function builder(iterable $attributes = []): AttributesBuilderInterface;
}
