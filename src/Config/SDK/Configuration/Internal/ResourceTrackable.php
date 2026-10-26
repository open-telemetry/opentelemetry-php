<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use OpenTelemetry\Config\SDK\Configuration\ResourceCollection;

/**
 * @internal
 */
interface ResourceTrackable
{
    public function trackResources(?ResourceCollection $resources): void;
}
