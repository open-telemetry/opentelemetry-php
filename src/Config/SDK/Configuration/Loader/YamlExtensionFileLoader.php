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
    /**
     * Nesting past this is treated as a recursive alias rather than a real document. The deepest
     * configuration this repository parses, upstream fixtures included, is 9 levels.
     */
    private const MAX_DEPTH = 64;

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

        $this->configuration->loadConfiguration(self::unreference($content, $path));

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
     *
     * An alias may also be recursive (`a: &x [*x]`), which ext-yaml resolves into a genuinely
     * self-referential array — symfony/yaml rejects those outright. Such a document has no finite
     * by-value copy, so depth is bounded and anything past the limit is reported as invalid:
     * recursing freely would exhaust memory, and that fatal is not catchable by the callers that
     * otherwise degrade to a noop configuration. Identity cannot be used to spot the cycle
     * instead, because comparing a self-referential array raises its own fatal.
     *
     * @throws InvalidArgumentException if $value nests deeper than {@see self::MAX_DEPTH}
     */
    private static function unreference(mixed $value, string $path, int $depth = 0): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        if ($depth > self::MAX_DEPTH) {
            throw new InvalidArgumentException(sprintf('The file "%s" contains a recursive YAML alias, or nests deeper than %d levels', $path, self::MAX_DEPTH));
        }

        $copy = [];
        foreach ($value as $key => $item) {
            $copy[$key] = self::unreference($item, $path, $depth + 1);
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
