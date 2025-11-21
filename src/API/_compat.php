<?php

declare(strict_types=1);

namespace OpenTelemetry\API;

use function class_exists;
use Composer\InstalledVersions;
use Composer\Semver\VersionParser;

if (
    // Provide a backwards-compatible Psr3 implementation when running under composer.
    class_exists(InstalledVersions::class, false)
    && InstalledVersions::satisfies(new VersionParser(), 'composer/composer', '~2.0')
) {
    require_once __DIR__ . '/Common/Compatibility/Psr3.php';
}
