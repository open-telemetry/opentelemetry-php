<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Loader;

use function error_get_last;
use function extension_loaded;
use InvalidArgumentException;
use function is_array;
use function is_string;
use function pathinfo;
use const PATHINFO_EXTENSION;
use function sprintf;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;

final class YamlExtensionFileLoader extends FileLoader
{
    public function __construct(private readonly ConfigurationLoader $configuration, FileLocatorInterface $locator, ?string $env = null)
    {
        parent::__construct($locator, $env);
    }

    #[\Override]
    public function load(mixed $resource, ?string $type = null): mixed
    {
        assert(extension_loaded('yaml'));

        $path = $this->locator->locate($resource);
        $this->configuration->addResource(new FileResource($path));

        if (($content = @\yaml_parse_file($path)) === false) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML: %s', $path, error_get_last()['message'] ?? ''));
        }

        $this->configuration->loadConfiguration(self::unreference($content));

        return null;
    }

    /**
     * A direct YAML alias (`b: *a`, as opposed to a merge key) becomes a PHP reference under
     * ext-yaml, where symfony/yaml yields independent copies. Environment substitution writes its
     * result back into the array it normalises, so through a reference the alias partner receives
     * already-substituted text and is then substituted a second time. That is not a no-op: an
     * escaped `$${FOO}` resolves to the value of `FOO` instead of staying the literal `${FOO}` the
     * author asked for, and a value that itself looks like a placeholder gets expanded again.
     * Copying by value here keeps aliases as the independent values the format describes.
     */
    private static function unreference(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        $copy = [];
        foreach ($value as $key => $item) {
            $copy[$key] = self::unreference($item);
        }

        return $copy;
    }

    #[\Override]
    public function supports(mixed $resource, ?string $type = null): bool
    {
        return extension_loaded('yaml')
            && is_string($resource)
            && match ($type ?? pathinfo($resource, PATHINFO_EXTENSION)) {
                'yaml', 'yml' => true,
                default => false,
            };
    }
}
