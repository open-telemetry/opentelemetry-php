<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\Context;

/**
 * @template T
 * @implements \OpenTelemetry\Config\SDK\Configuration\ComponentPlugin<T>
 *
 * @internal
 */
final readonly class ComponentPlugin implements \OpenTelemetry\Config\SDK\Configuration\ComponentPlugin
{

    /**
     * @param array $properties resolved properties according to component provider config
     * @param ComponentProvider<T> $provider component provider used to create the component
     */
    public function __construct(
        private array $properties,
        private ComponentProvider $provider,
    ) {
    }

    public function create(Context $context): mixed
    {
        return $this->provider->createPlugin($this->properties, $context);
    }
}
