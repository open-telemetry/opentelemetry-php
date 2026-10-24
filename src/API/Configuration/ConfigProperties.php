<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Configuration;

/**
 * @experimental The spec's ConfigProperties is a view of one mapping node with typed accessors.
 * Those accessors will be added alongside get(), which stays.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/api.md#configproperties
 */
interface ConfigProperties
{
    public function get(string $id): mixed;
}
