<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Loader;

use function assert;
use function class_exists;
use InvalidArgumentException;
use function is_string;
use function pathinfo;
use const PATHINFO_EXTENSION;
use function sprintf;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final class YamlSymfonyFileLoader extends FileLoader
{
    public function __construct(private readonly ConfigurationLoader $configuration, FileLocatorInterface $locator, ?string $env = null)
    {
        parent::__construct($locator, $env);
    }

    public function load(mixed $resource, ?string $type = null): mixed
    {
        assert(class_exists(Yaml::class));

        $path = $this->locator->locate($resource);
        $this->configuration->addResource(new FileResource($path));

        try {
            $content = Yaml::parseFile($path, Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE);
        } catch (ParseException $e) {
            throw new InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML: %s', $path, $e->getMessage()), 0, $e);
        }

        $this->configuration->loadConfiguration($content);

        return null;
    }

    public function supports(mixed $resource, ?string $type = null): bool
    {
        return class_exists(Yaml::class)
            && is_string($resource)
            && match ($type ?? pathinfo($resource, PATHINFO_EXTENSION)) {
                'yaml', 'yml' => true,
                default => false,
            };
    }
}
