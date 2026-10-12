<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK;

use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Instrumentation\AutoInstrumentation\ConfigurationRegistry;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;

/**
 * @deprecated Use {@see Configuration::parseFile()} and
 *             {@see Configuration::getInstrumentationConfig()} instead. Instrumentation
 *             configuration is a node of the same document as the SDK configuration
 *             ({@see https://github.com/open-telemetry/opentelemetry-configuration/pull/91}), so a
 *             separate parse entry point reads and env-substitutes the file twice.
 */
final class Instrumentation
{
    private function __construct(
        private readonly Configuration $configuration,
    ) {
    }

    public function create(Context $context = new Context()): ConfigurationRegistry
    {
        /** @var ConfigurationRegistry */
        return $this->configuration->getInstrumentationConfig($context);
    }

    /**
     * @param string|list<string> $file
     */
    public static function parseFile(
        string|array $file,
        ?string $cacheFile = null,
        bool $debug = true,
        ?EnvReader $envReader = null,
    ): Instrumentation {
        return new self(Configuration::parseFile($file, $cacheFile, $debug, $envReader));
    }
}
