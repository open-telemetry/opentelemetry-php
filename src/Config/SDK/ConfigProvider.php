<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK;

use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Configuration\ConfigProperties;
use OpenTelemetry\API\Configuration\ConfigProviderInterface;
use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\API\Configuration\Noop\NoopConfigProperties;
use OpenTelemetry\SDK\Common\Configuration\Configuration as EnvConfiguration;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use function sprintf;
use Throwable;

/**
 * SPI service giving {@see \OpenTelemetry\SDK\SdkAutoloader} access to file-based instrumentation
 * configuration without a hard dependency on this package. Registered under
 * {@see ConfigProviderInterface}; the autoloader falls back to the API's noop provider when this
 * package is absent.
 *
 * @experimental
 * @todo Remove once the SDK can parse both configuration roots in one pass, which SPI-based
 *       instrumentation registration in SDK 2.0 makes possible. The indirection exists only
 *       because `open-telemetry/sdk` merely suggests `open-telemetry/sdk-configuration`.
 */
final class ConfigProvider implements ConfigProviderInterface
{
    use LogsMessagesTrait;

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    #[\Override]
    public function getInstrumentationConfig(Context $context = new Context()): ConfigProperties
    {
        if (!EnvConfiguration::has(Variables::OTEL_CONFIG_FILE)) {
            return new NoopConfigProperties();
        }

        try {
            return Configuration::parseFile(EnvConfiguration::getList(Variables::OTEL_CONFIG_FILE))
                ->getInstrumentationConfig($context);
        } catch (Throwable $e) {
            self::logWarning(sprintf('Error during opentelemetry initialization: %s', $e->getMessage()), ['exception' => $e]);

            return new NoopConfigProperties();
        }
    }
}
