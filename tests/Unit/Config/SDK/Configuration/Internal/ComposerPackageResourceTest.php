<?php

declare(strict_types=1);

namespace OpenTelemetry\Test\Unit\Config\SDK\Configuration\Internal;

use OpenTelemetry\Config\SDK\Configuration\Internal\ComposerPackageResource;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ComposerPackageResource::class)]
final class ComposerPackageResourceTest extends TestCase
{
    public function test_package_name_is_stored(): void
    {
        $resource = new ComposerPackageResource('phpunit/phpunit');
        $this->assertSame('phpunit/phpunit', $resource->packageName);
    }

    public function test_version_is_set_for_installed_package(): void
    {
        $resource = new ComposerPackageResource('phpunit/phpunit');
        $this->assertIsString($resource->version);
        $this->assertNotEmpty($resource->version);
    }

    public function test_version_is_false_for_uninstalled_package(): void
    {
        $resource = new ComposerPackageResource('nonexistent/package-that-does-not-exist');
        $this->assertFalse($resource->version);
    }

    public function test_is_fresh_returns_true_when_version_unchanged(): void
    {
        $resource = new ComposerPackageResource('phpunit/phpunit');
        $this->assertTrue($resource->isFresh(time()));
    }

    public function test_is_fresh_returns_true_for_nonexistent_package(): void
    {
        $resource = new ComposerPackageResource('nonexistent/package-that-does-not-exist');
        // version is false, getVersion also returns false, so they match
        $this->assertTrue($resource->isFresh(time()));
    }

    public function test_to_string_returns_prefixed_package_name(): void
    {
        $resource = new ComposerPackageResource('phpunit/phpunit');
        $this->assertSame('composer.phpunit/phpunit', (string) $resource);
    }
}
