<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors\Host;
use OpenTelemetry\SemConv\ResourceAttributes;
use OpenTelemetry\Tests\TestState;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;
use PHPUnit\Framework\TestCase;

#[CoversClass(Host::class)]
class HostTest extends TestCase
{
    use TestState;

    private ?string $plantedDir = null;

    #[\Override]
    public function setUp(): void
    {
        //reset vfs between tests
        vfsStream::setup('/');
    }

    #[\Override]
    public function tearDown(): void
    {
        if ($this->plantedDir !== null) {
            array_map(unlink(...), glob($this->plantedDir . '/*') ?: []);
            rmdir($this->plantedDir);
        }
    }

    public function test_host_get_resource(): void
    {
        $resourceDetector = new Host();
        $resource = $resourceDetector->getResource();

        $this->assertStringMatchesFormat('https://opentelemetry.io/schemas/%d.%d.%d', $resource->getSchemaUrl() ?? '');
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::OS_TYPE));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::OS_DESCRIPTION));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::OS_NAME));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::OS_VERSION));
    }

    #[DataProvider('hostIdData')]
    public function test_host_id_filesystem(string $os, array $files, ?string $expectedId): void
    {
        $root = vfsStream::setup('/', null, $files);
        $resourceDetector = new Host($root->url(), $os);
        $resource = $resourceDetector->getResource();

        if ($expectedId === null) {
            $this->assertFalse($resource->getAttributes()->has(ResourceAttributes::HOST_ID));

            return;
        }

        $hostId = $resource->getAttributes()->get(ResourceAttributes::HOST_ID);
        $this->assertIsString($hostId);
        $this->assertSame($expectedId, $hostId);
    }

    public static function hostIdData(): array
    {
        $etc_machineid = [
            'etc' => [
                'machine-id' => '1234567890',
            ],
        ];
        $varLibDbus = [
            'var' => [
                'lib' => [
                    'dbus' => [
                        'machine-id' => '0987654321',
                    ],
                ],
            ],
        ];
        $etc_hostid = [
            'etc' => [
                'hostid' => '1234567890',
            ],
        ];

        return [
            ['Linux', [], null],
            ['Linux', $etc_machineid, '1234567890'],
            ['Linux', array_merge($etc_machineid, $varLibDbus), '1234567890'],
            ['Linux', $etc_machineid, '1234567890'],
            ['BSD', $etc_hostid, '1234567890'],
        ];
    }

    #[DataProvider('nonReadableFileProvider')]
    public function test_file_not_readable(string $os, vfsStreamDirectory $root): void
    {
        $resourceDetector = new Host($root->url(), $os);
        $resource = $resourceDetector->getResource();

        $hostId = $resource->getAttributes()->get(ResourceAttributes::HOST_ID);
        $this->assertNull($hostId);
    }

    public static function nonReadableFileProvider(): array
    {
        $root = vfsStream::setup('/');
        $etc = vfsStream::newDirectory('etc')->at($root);
        vfsStream::newFile('machine-id')
            ->at($etc)
            ->setContent('you-cant-see-me')
            ->chmod(0222);
        vfsStream::newFile('hostid')
            ->at($etc)
            ->setContent('you-cant-see-me')
            ->chmod(0222);

        return [
            ['Linux', $root],
            ['BSD', $root],
        ];
    }

    /**
     * @see https://github.com/open-telemetry/semantic-conventions/blob/v1.44.0/docs/resource/host.md#host
     */
    #[DataProvider('commandOsProvider')]
    #[RequiresOperatingSystem('^(?!WIN)')]
    public function test_commands_are_not_resolved_through_path(string $os): void
    {
        $this->plantedDir = sys_get_temp_dir() . '/' . uniqid('otel-host-', true);
        mkdir($this->plantedDir);
        foreach (['ioreg', 'awk', 'which', 'kenv', 'powershell.exe', 'reg.exe'] as $name) {
            $script = $this->plantedDir . '/' . $name;
            file_put_contents($script, sprintf(
                "#!/bin/sh\necho %s >> %s/ran\necho '\"IOPlatformUUID\" = \"planted\"'\necho planted\n",
                $name,
                $this->plantedDir,
            ));
            chmod($script, 0755);
        }
        $this->setEnvironmentVariable('PATH', $this->plantedDir . PATH_SEPARATOR . getenv('PATH'));

        $root = vfsStream::setup('/');
        $resource = (new Host($root->url(), $os))->getResource();

        $this->assertFileDoesNotExist($this->plantedDir . '/ran');
        $this->assertNotSame('planted', $resource->getAttributes()->get(ResourceAttributes::HOST_ID));
    }

    public static function commandOsProvider(): array
    {
        return [
            ['Darwin'],
            ['BSD'],
            ['Windows'],
        ];
    }
}
