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
 *             ({@see https://github.com/open-telemetry/opentelemetry-configuration/pull/91}), so it
 *             does not need a parse entry point of its own: one {@see Configuration} serves both
 *             roots, and reaching them separately costs an extra read of the same file whenever the
 *             SDK root came from the on-disk cache.
 *
 * @todo Remove before 1.0 of `open-telemetry/sdk-configuration`. Kept for now as a transition for
 *       instrumentations still on this entry point — the package is 0.x, so this is a courtesy
 *       rather than a compatibility guarantee.
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
