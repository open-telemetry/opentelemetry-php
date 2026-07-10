<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use Composer\InstalledVersions;
use OpenTelemetry\SDK\Resource\Detectors\Composer;
use OpenTelemetry\SemConv\ResourceAttributes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

#[CoversClass(Composer::class)]
class ComposerTest extends TestCase
{
    private const REAL_INSTALLED_PHP = __DIR__ . '/../../../../../vendor/composer/installed.php';

    #[\Override]
    protected function tearDown(): void
    {
        InstalledVersions::reload(require self::REAL_INSTALLED_PHP);
        (new ReflectionProperty(InstalledVersions::class, 'canGetVendors'))->setValue(null, null);
    }

    public function test_composer_get_resource(): void
    {
        $resourceDetector = new Composer();
        $resource = $resourceDetector->getResource();
        $name = 'open-telemetry/opentelemetry';
        $version = InstalledVersions::getPrettyVersion($name);

        $this->assertStringMatchesFormat('https://opentelemetry.io/schemas/%d.%d.%d', $resource->getSchemaUrl() ?? '');
        $this->assertSame($name, $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
        $this->assertSame($version, $resource->getAttributes()->get(ResourceAttributes::SERVICE_VERSION));
    }

    public function test_composer_detector(): void
    {
        $resource = (new Composer())->getResource();

        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
        $this->assertNotNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_VERSION));
    }

    public function test_reports_service_version_when_set(): void
    {
        self::reloadInstalledVersions('foo/bar', '2.3.4');

        $resource = (new Composer())->getResource();

        $this->assertSame('foo/bar', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
        $this->assertSame('2.3.4', $resource->getAttributes()->get(ResourceAttributes::SERVICE_VERSION));
    }

    public function test_omits_service_version_when_composer_reports_placeholder(): void
    {
        self::reloadInstalledVersions('foo/bar', '1.0.0+no-version-set');

        $resource = (new Composer())->getResource();

        $this->assertSame('foo/bar', $resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_VERSION));
    }

    public function test_omits_service_name_when_composer_reports_placeholder(): void
    {
        self::reloadInstalledVersions('__root__', '2.3.4');

        $resource = (new Composer())->getResource();

        $this->assertNull($resource->getAttributes()->get(ResourceAttributes::SERVICE_NAME));
        $this->assertSame('2.3.4', $resource->getAttributes()->get(ResourceAttributes::SERVICE_VERSION));
    }

    private static function reloadInstalledVersions(string $name, string $prettyVersion): void
    {
        InstalledVersions::reload([
            'root' => [
                'name' => $name,
                'pretty_version' => $prettyVersion,
                'version' => $prettyVersion,
                'reference' => null,
                'type' => 'library',
                'install_path' => __DIR__,
                'aliases' => [],
                'dev' => true,
            ],
            'versions' => [],
        ]);

        // InstalledVersions::getRootPackage() otherwise re-reads the real
        // vendor/composer/installed.php via the registered class loader, ignoring
        // the data just passed to reload(); disabling this lookup forces it to use
        // the reloaded dataset instead.
        (new ReflectionProperty(InstalledVersions::class, 'canGetVendors'))->setValue(null, false);
    }
}
