<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use function getcwd;
use function in_array;
use InvalidArgumentException;
use OpenTelemetry\Config\SDK\Configuration\Loader\YamlExtensionFileLoader;
use OpenTelemetry\Config\SDK\Configuration\Loader\YamlSymfonyFileLoader;
use OpenTelemetry\Config\SDK\Configuration\ResourceCollection;
use function pathinfo;
use const PATHINFO_EXTENSION;
use function sprintf;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;

/**
 * @internal
 */
final class ConfigurationFileLoader
{
    /**
     * @return array loaded configurations, one entry per file
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/data-model.md#yaml-file-format
     */
    public static function load(string $file, ?ResourceCollection $resources = null): array
    {
        if (!in_array(pathinfo($file, PATHINFO_EXTENSION), ['yaml', 'yml'], true)) {
            throw new InvalidArgumentException(sprintf('Configuration file "%s" must have a .yaml or .yml extension', $file));
        }

        $loader = new ConfigurationLoader($resources);
        $cwd = getcwd() ?: [];
        $locator = new FileLocator($cwd);
        $fileLoader = new DelegatingLoader(new LoaderResolver([
            new YamlSymfonyFileLoader($loader, $locator),
            new YamlExtensionFileLoader($loader, $locator),
        ]));

        $fileLoader->load($file);

        return $loader->getConfigurations();
    }
}
