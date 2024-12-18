<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration;

use function class_exists;
use Exception;
use function getcwd;
use function is_file;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvResourceChecker;
use OpenTelemetry\Config\SDK\Configuration\Internal\CompiledConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Internal\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Internal\ConfigurationLoader;
use OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\ArrayNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\BooleanNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\FloatNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\IntegerNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\ScalarNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\StringNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\VariableNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\ResourceCollection;
use OpenTelemetry\Config\SDK\Configuration\Internal\TrackingEnvReader;
use OpenTelemetry\Config\SDK\Configuration\Loader\YamlExtensionFileLoader;
use OpenTelemetry\Config\SDK\Configuration\Loader\YamlSymfonyFileLoader;
use function serialize;
use function sprintf;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Config\Resource\SelfCheckingResourceChecker;
use Symfony\Component\Config\ResourceCheckerConfigCache;
use Symfony\Component\VarExporter\VarExporter;
use Throwable;
use function var_export;

/**
 * @template T
 */
final class ConfigurationFactory
{

    private readonly CompiledConfigurationFactory $compiledFactory;

    /**
     * @param iterable<ComponentProvider> $componentProviders
     * @param ComponentProvider<T> $rootComponent
     * @param EnvReader $envReader
     */
    public function __construct(
        private readonly iterable $componentProviders,
        private readonly ComponentProvider $rootComponent,
        private readonly EnvReader $envReader,
    ) {
        $this->compiledFactory = $this->compileFactory();
    }

    /**
     * @param array $configs configs to process
     * @param ResourceCollection|null $resources resources that can be used for cache invalidation
     * @throws InvalidConfigurationException if the configuration is invalid
     * @return ComponentPlugin<T> processed component plugin
     */
    public function process(array $configs, ?ResourceCollection $resources = null): ComponentPlugin
    {
        return $this->compiledFactory
            ->process($configs, $resources);
    }

    /**
     * @param string|list<string> $file path(s) to parse
     * @param string|null $cacheFile path to cache parsed configuration to
     * @param bool $debug will check for cache freshness if debug mode enabled
     * @throws Exception if loading of a configuration file fails for any reason
     * @throws InvalidConfigurationException if the configuration is invalid
     * @throws Throwable if a cache file is given and a non-serializable component provider is used
     * @return ComponentPlugin parsed component plugin
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/configuration/file-configuration.md#parse
     * @psalm-suppress PossiblyNullReference
     */
    public function parseFile(
        string|array $file,
        ?string $cacheFile = null,
        bool $debug = true,
    ): ComponentPlugin {
        $cache = null;
        $resources = null;
        if ($cacheFile !== null) {
            $cache = new ResourceCheckerConfigCache($cacheFile, [
                new SelfCheckingResourceChecker(),
                new EnvResourceChecker($this->envReader),
            ]);
            if (is_file($cache->getPath())
                && ($configuration = @include $cache->getPath()) instanceof ComponentPlugin
                && (!$debug || $cache->isFresh())) {
                return $configuration;
            }
            $resources = new ResourceCollection();
            $resources->addClassResource(ComponentPlugin::class);
            $resources->addClassResource(VarExporter::class);
        }

        $loader = new ConfigurationLoader($resources);
        $locator = new FileLocator(getcwd());
        $fileLoader = new DelegatingLoader(new LoaderResolver([
            new YamlSymfonyFileLoader($loader, $locator),
            new YamlExtensionFileLoader($loader, $locator),
        ]));

        foreach ((array) $file as $path) {
            $fileLoader->load($path);
        }

        $configuration = $this->compiledFactory
            ->process($loader->getConfigurations(), $resources);

        $cache?->write(
            class_exists(VarExporter::class)
                ? sprintf('<?php return %s;', VarExporter::export($configuration))
                : sprintf('<?php return unserialize(%s);', var_export(serialize($configuration), true)),
            $resources->toArray()
        );

        return $configuration;
    }

    private function compileFactory(): CompiledConfigurationFactory
    {
        $envReader = new TrackingEnvReader($this->envReader);
        $normalizations = [
            // Parse MUST perform environment variable substitution.
            new EnvSubstitutionNormalization($envReader),
        ];

        $builder = new NodeBuilder();
        $builder->setNodeClass('variable', VariableNodeDefinition::class);
        $builder->setNodeClass('scalar', ScalarNodeDefinition::class);
        $builder->setNodeClass('boolean', BooleanNodeDefinition::class);
        $builder->setNodeClass('integer', IntegerNodeDefinition::class);
        $builder->setNodeClass('float', FloatNodeDefinition::class);
        $builder->setNodeClass('array', ArrayNodeDefinition::class);
        $builder->setNodeClass('string', StringNodeDefinition::class);

        $registry = new ComponentProviderRegistry($normalizations, $builder);

        foreach ($this->componentProviders as $provider) {
            $registry->register($provider);
        }

        $root = $this->rootComponent->getConfig($registry, $builder);
        foreach ($normalizations as $normalization) {
            $normalization->apply($root);
        }

        $node = $root->getNode(forceRootNode: true);

        return new CompiledConfigurationFactory(
            $this->rootComponent,
            $node,
            [
                $registry,
                $envReader,
            ],
        );
    }
}
