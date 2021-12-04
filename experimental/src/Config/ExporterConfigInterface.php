<?php

declare(strict_types=1);

namespace OpenTelemetry\Experimental\Config;

interface ExporterConfigInterface
{
    public static function provides(string $exporterName): bool;
    public function getName(): string;
}
