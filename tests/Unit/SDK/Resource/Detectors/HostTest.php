<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SemConv\ResourceAttributes;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\OpenTelemetry\SDK\Resource\Detectors\Host::class)]
class HostTest extends TestCase
{
    public function test_host_get_resource(): void
    {
        $resourceDetector = new Detectors\Host();
        $resource = $resourceDetector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::HOST_NAME));
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::HOST_ARCH));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('hostIdData')]
    public function test_host_id_filesystem(string $os, array $files, ?string $expectedId): void
    {
        $root = vfsStream::setup('/', null, $files);
        $resourceDetector = new Detectors\Host($root->url(), $os);
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
}
