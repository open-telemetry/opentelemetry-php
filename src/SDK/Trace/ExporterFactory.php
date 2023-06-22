<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use InvalidArgumentException;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Registry;
use RuntimeException;

class ExporterFactory
{
    /**
     * @throws RuntimeException
     */
    public function create(): ?SpanExporterInterface
    {
        $exporters = Configuration::getList(Variables::OTEL_TRACES_EXPORTER);
        if (1 !== count($exporters)) {
            throw new InvalidArgumentException(sprintf('Configuration %s requires exactly 1 exporter', Variables::OTEL_TRACES_EXPORTER));
        }
        $exporter = $exporters[0];
        if ($exporter === 'none') {
            return null;
        }
        $factory = Registry::spanExporterFactory($exporter);

        return $factory->create();
    }
}
