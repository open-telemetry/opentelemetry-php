<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use function getcwd;
use OpenTelemetry\Config\SDK\Configuration\Loader\YamlExtensionFileLoader;
use OpenTelemetry\Config\SDK\Configuration\Loader\YamlSymfonyFileLoader;
use OpenTelemetry\Config\SDK\Configuration\ResourceCollection;
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
     */
    public static function load(string $file, ?ResourceCollection $resources = null): array
    {
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
