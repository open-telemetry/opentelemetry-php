<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SemConv\ResourceAttributes;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Resource\Detectors\Host
 */
class HostTest extends TestCase
{
    public function test_host_get_resource(): void
    {
        $resouceDetector = new Detectors\Host();
        $resource = $resouceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::HOST_ID));
    }

    public function test_host_parse_macos_id(): void
    {
        $out = 'IOPlatformUUID=1234567890';
        $hostId = Detectors\Host::parseMacOsId($out);
        $this->assertIsString($hostId);
        $this->assertSame('1234567890', $hostId);
    }

    public function test_host_parse_windows_id(): void
    {
        $out = 'HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Cryptography\MachineGuid    REG_SZ    1234567890';
        $hostId = Detectors\Host::parseWindowsId($out);
        $this->assertIsString($hostId);
        $this->assertSame('1234567890', $hostId);
    }

    /**
     * @dataProvider hostIdData
     */
    public function test_host_id_linux(array $files, string $expectedId): void
    {
        $root = vfsStream::setup('/', null, $files);
        $resouceDetector = new Detectors\Host($root->url());
        $resource = $resouceDetector->getResource();
        $hostId = $resource->getAttributes()->get(ResourceAttributes::HOST_ID);
        $this->assertIsString($hostId);
        $this->assertSame($expectedId, $hostId);
    }

    public static function hostIdData(): array
    {
        $etc = [
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

        return [
            [[], ''],
            [$etc, '1234567890'],
            [array_merge($etc, $varLibDbus), '1234567890'],
            [$varLibDbus, '0987654321'],
        ];
    }
}
