<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Instrumentation;

use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;

final class InstrumentationScopeFactory implements InstrumentationScopeFactoryInterface
{
    private AttributesFactoryInterface $attributesFactory;

    public function __construct(AttributesFactoryInterface $attributesFactory)
    {
        $this->attributesFactory = $attributesFactory;
    }

    public function create(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = []
    ): InstrumentationScopeInterface {
        return new InstrumentationScope(
            $name,
            $version,
            $schemaUrl,
            $this->attributesFactory->builder($attributes)->build(),
        );
    }
}
