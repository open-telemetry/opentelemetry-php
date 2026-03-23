<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Distribution;

use Override;

final class DistributionRegistry implements DistributionProperties
{
    private array $distributionConfigurations = [];

    public function add(DistributionConfiguration $distributionConfiguration): self
    {
        $this->distributionConfigurations[$distributionConfiguration::class] = $distributionConfiguration;

        return $this;
    }

    #[Override]
    public function getDistributionConfiguration(string $distribution): ?DistributionConfiguration
    {
        return $this->distributionConfigurations[$distribution] ?? null;
    }
}
