<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use function class_exists;
use Composer\InstalledVersions;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SemConv\Attributes\ServiceAttributes;
use OpenTelemetry\SemConv\Version;

/**
 * Detect service name and version of root package. Not included in `all` detectors.
 */
final class Composer implements ResourceDetectorInterface
{
    /**
     * Placeholder name that Composer reports for a root package with no `name`
     * set in composer.json (Composer\Package\Loader\RootPackageLoader).
     */
    private const UNSET_NAME = '__root__';

    /**
     * Placeholder version that Composer reports for a root package with no explicit
     * version (Composer\Package\RootPackage::DEFAULT_PRETTY_VERSION).
     */
    private const UNSET_VERSION = '1.0.0+no-version-set';

    #[\Override]
    public function getResource(): ResourceInfo
    {
        if (!class_exists(InstalledVersions::class)) {
            return ResourceInfoFactory::emptyResource();
        }
        $rootPackage = InstalledVersions::getRootPackage();

        $attributes = [];

        // Only report service.name when the root package has an explicit name.
        // Composer substitutes a placeholder ("__root__") when none is set. See #1320.
        if ($rootPackage['name'] !== self::UNSET_NAME) {
            $attributes[ServiceAttributes::SERVICE_NAME] = $rootPackage['name'];
        }

        // Only report service.version when the root package has an explicit version.
        // Composer substitutes a placeholder ("1.0.0+no-version-set") when none is set,
        // and other OpenTelemetry SDKs do not set service.version by default. See #1320.
        if ($rootPackage['pretty_version'] !== self::UNSET_VERSION) {
            $attributes[ServiceAttributes::SERVICE_VERSION] = $rootPackage['pretty_version'];
        }

        return ResourceInfo::create(Attributes::create($attributes), Version::VERSION_1_38_0->url());
    }
}
