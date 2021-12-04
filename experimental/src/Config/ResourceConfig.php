<?php

declare(strict_types=1);

namespace OpenTelemetry\Experimental\Config;

class ResourceConfig implements ConfigInterface
{
    public ResourceLimitsConfig $limits;
    public array $attributes = [];

    public function __construct(array $userConfig, array $environmentConfig)
    {
        $this->limits = new ResourceLimitsConfig($userConfig, $environmentConfig);
        foreach (explode(',', ($environmentConfig['OTEL_RESOURCE_ATTRIBUTES'] ?? '')) as $pair) {
            list($key, $value) = explode('=', $pair, 2);
            $this->attributes[$key] = $value;
        }
        foreach ($userConfig['resource.attributes'] ?? [] as $name => $value) {
            $this->attributes[$name] = $value;
        }
    }
}
