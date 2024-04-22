<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Loader;

use Symfony\Component\Config\Resource\ResourceInterface;

interface ConfigurationLoader
{

    public function loadConfiguration(mixed $configuration): void;

    public function addResource(ResourceInterface $resource): void;
}
