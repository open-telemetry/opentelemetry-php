<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration;

use function class_exists;
use Exception;
use function is_file;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\API\Configuration\Config\ComponentPlugin;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvReader;
use OpenTelemetry\Config\SDK\Configuration\Environment\EnvResourceChecker;
use OpenTelemetry\Config\SDK\Configuration\Internal\CompiledConfigurationFactory;
use OpenTelemetry\Config\SDK\Configuration\Internal\ComponentProviderRegistry;
use OpenTelemetry\Config\SDK\Configuration\Internal\ConfigurationFileLoader;
use OpenTelemetry\Config\SDK\Configuration\Internal\EnvSubstitutionNormalization;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\ArrayNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\BooleanNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\EnumNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\FloatNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\IntegerNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\ScalarNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\StringNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NodeDefinition\VariableNodeDefinition;
use OpenTelemetry\Config\SDK\Configuration\Internal\NormalizationsAware;
use OpenTelemetry\Config\SDK\Configuration\Internal\TrackingEnvReader;
use function serialize;
use function sprintf;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Resource\SelfCheckingResourceChecker;
use Symfony\Component\Config\ResourceCheckerConfigCache;
use Symfony\Component\VarExporter\VarExporter;
use Throwable;
use function umask;
use function var_export;

/**
 * @template T
 */
final class ConfigurationFactory
{
    use LogsMessagesTrait;

    private readonly CompiledConfigurationFactory $compiledFactory;

    /**
     * @param iterable<ComponentProvider> $componentProviders
     * @param ComponentProvider<T> $rootComponent
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
     * @return ComponentPlugin parsed component plugin
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/configuration/sdk.md#parse
     */
    public function parseFile(
        string|array $file,
        ?string $cacheFile = null,
        bool $debug = true,
    ): ComponentPlugin {
        if ($cacheFile === null) {
            return $this->compiledFactory->process(ConfigurationFileLoader::load($file));
        }

        $cache = new ResourceCheckerConfigCache($cacheFile, [
            new SelfCheckingResourceChecker(),
            new EnvResourceChecker($this->envReader),
        ]);
        if (($configuration = self::includeCache($cache->getPath())) instanceof ComponentPlugin
            && (!$debug || $cache->isFresh())) {
            return $configuration;
        }
        $resources = new Internal\ResourceCollection();
        $resources->addClassResource(ComponentPlugin::class);
        $resources->addClassResource(VarExporter::class);

        $configuration = $this->compiledFactory
            ->process(ConfigurationFileLoader::load($file, $resources), $resources);
        self::writeCache($cache, $configuration, $resources);

        return $configuration;
    }

    private static function includeCache(string $path): mixed
    {
        if (!is_file($path)) {
            return null;
        }

        try {
            return @include $path;
        } catch (Throwable) {
            return null;
        }
    }

    private static function writeCache(ResourceCheckerConfigCache $cache, ComponentPlugin $configuration, Internal\ResourceCollection $resources): void
    {
        // The cache holds substituted values, which may be secrets. umask is process-wide, so this
        // can race with file creation in other threads of a ZTS build.
        $umask = umask(0o077);

        try {
            $cache->write(
                class_exists(VarExporter::class)
                    ? sprintf('<?php return %s;', VarExporter::export($configuration))
                    : sprintf('<?php return unserialize(%s);', var_export(serialize($configuration), true)),
                $resources->toArray()
            );
        } catch (Throwable $e) {
            self::logWarning(sprintf('Failed to write configuration cache "%s"', $cache->getPath()), ['exception' => $e]);
        } finally {
            umask($umask);
        }
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
        $builder->setNodeClass('enum', EnumNodeDefinition::class);

        $registry = new ComponentProviderRegistry($normalizations, $builder);

        foreach ($this->componentProviders as $provider) {
            $registry->register($provider);
        }

        $node = $this->rootComponent->getConfig($registry, $builder)->getNode(forceRootNode: true);
        if ($node instanceof NormalizationsAware) {
            $node->setNormalizations($normalizations);
        }

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
