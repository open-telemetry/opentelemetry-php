<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\API\Configuration\Context;

/**
 * @template T
 * @implements \OpenTelemetry\API\Configuration\Config\ComponentPlugin<T>
 *
 * @internal
 */
final class ComponentPlugin implements \OpenTelemetry\API\Configuration\Config\ComponentPlugin
{
    /**
     * @param array $properties resolved properties according to component provider config
     * @param ComponentProvider<T> $provider component provider used to create the component
     */
    public function __construct(
        private readonly array $properties,
        private readonly ComponentProvider $provider,
    ) {
    }

    public function create(Context $context): mixed
    {
        return $this->provider->createPlugin($this->properties, $context);
    }
}
