<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration;

/**
 * @experimental Will be reshaped to match the spec's ConfigProperties, a view of one mapping node
 * with typed accessors.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/api.md#configproperties
 */
interface ConfigProperties
{
    public function get(string $id): mixed;
}
