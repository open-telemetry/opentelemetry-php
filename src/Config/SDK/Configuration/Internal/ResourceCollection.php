<?php

declare(strict_types=1);

namespace OpenTelemetry\Config\SDK\Configuration\Internal;

use const DIRECTORY_SEPARATOR;
use function realpath;
use ReflectionClass;
use ReflectionException;
use function str_starts_with;
use function strlen;
use Symfony\Component\Config\Resource\ClassExistenceResource;
use Symfony\Component\Config\Resource\ComposerResource;
use Symfony\Component\Config\Resource\DirectoryResource;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Resource\GlobResource;
use Symfony\Component\Config\Resource\ReflectionClassResource;
use Symfony\Component\Config\Resource\ResourceInterface;

/**
 * @internal
 */
final class ResourceCollection implements \OpenTelemetry\Config\SDK\Configuration\ResourceCollection
{

    /** @var array<ResourceInterface> */
    private array $resources = [];
    private readonly ComposerResource $composerResource;
    /** @var list<string> */
    private readonly array $vendors;

    /**
     * @psalm-suppress PropertyTypeCoercion
     */
    public function __construct()
    {
        $this->composerResource = new ComposerResource();
        $this->vendors = $this->composerResource->getVendors();
    }

    /**
     * @psalm-suppress PossiblyInvalidArgument
     */
    public function addClassResource(object|string $class): void
    {
        try {
            $reflection = new ReflectionClass($class);
            if ($this->isInVendors($reflection->getFileName())) {
                return;
            }

            $this->addResource(new ReflectionClassResource($reflection, $this->vendors));
        } catch (ReflectionException) {
            //@todo $class could be an object here?
            $this->addResource(new ClassExistenceResource($class, false));
        }
    }

    public function addResource(ResourceInterface $resource): void
    {
        $path = match (true) {
            $resource instanceof FileResource => $resource->getResource(),
            $resource instanceof GlobResource => $resource->getPrefix(),
            $resource instanceof DirectoryResource => $resource->getResource(),
            default => null,
        };

        if ($path !== null && $this->isInVendors($path)) {
            return;
        }

        $this->resources[(string) $resource] = $resource;
    }

    /**
     * @return list<ResourceInterface>
     */
    public function toArray(): array
    {
        return array_values($this->resources);
    }

    /**
     * @see ReflectionClassResource::loadFiles()
     */
    private function isInVendors(string $path): bool
    {
        $path = realpath($path) ?: $path;

        foreach ($this->vendors as $vendor) {
            $c = $path[strlen((string) $vendor)] ?? null;
            if (str_starts_with($path, (string) $vendor) && ($c === '/' || $c === DIRECTORY_SEPARATOR)) {
                $this->addResource($this->composerResource);

                return true;
            }
        }

        return false;
    }
}
