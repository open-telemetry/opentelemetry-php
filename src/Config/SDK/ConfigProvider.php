<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK;

use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\API\Configuration\ConfigProviderInterface;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Configuration\Noop\NoopConfigProperties;
use OpenTelemetry\SDK\Common\Configuration\Configuration as EnvConfiguration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use Psr\Log\LogLevel;
use function sprintf;
use Throwable;

/**
 * Reads file-based instrumentation configuration from `OTEL_CONFIG_FILE`, registered under
 * {@see ConfigProviderInterface} via SPI.
 *
 * This is the spec's entry point for instrumentations that read their own configuration without
 * depending on the SDK. It is deliberately not on {@see \OpenTelemetry\SDK\SdkAutoloader}'s path:
 * the autoloader parses the document once and reads both configuration roots off that single
 * {@see Configuration}, so resolving this service there would cost it a second parse.
 *
 * @experimental
 */
final class ConfigProvider implements ConfigProviderInterface
{
    #[\Override]
    public function getInstrumentationConfig(Context $context = new Context()): ConfigProperties
    {
        if (!EnvConfiguration::has(Variables::OTEL_CONFIG_FILE)) {
            return new NoopConfigProperties();
        }

        try {
            return Configuration::parseInstrumentationConfig(EnvConfiguration::getString(Variables::OTEL_CONFIG_FILE), $context);
        } catch (Throwable $e) {
            $context->logger->log(LogLevel::WARNING, sprintf('Error during opentelemetry initialization: %s', $e->getMessage()), ['exception' => $e]);

            return new NoopConfigProperties();
        }
    }
}
