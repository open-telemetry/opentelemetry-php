<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Distribution;

interface DistributionProperties
{
    /**
     * @template C of DistributionConfiguration
     * @param class-string<C> $distribution
     * @return C|null
     */
    public function getDistributionConfiguration(string $distribution): ?DistributionConfiguration;
}
