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
        $out = <<<END
        +-o J293AP  <class IOPlatformExpertDevice, id 0x100000227, registered, matched,$
        {
          "IOPolledInterface" = "AppleARMWatchdogTimerHibernateHandler is not seria$
          "#address-cells" = <02000000>
          "AAPL,phandle" = <01000000>
          "serial-number" = <432123465233514651303544000000000000000000000000000000$
          "IOBusyInterest" = "IOCommand is not serializable"
          "target-type" = <"J293">
          "platform-name" = <743831303300000000000000000000000000000000000000000000$
          "secure-root-prefix" = <"md">
          "name" = <"device-tree">
          "region-info" = <4c4c2f41000000000000000000000000000000000000000000000000$
          "manufacturer" = <"Apple Inc.">
          "compatible" = <"J293AP","MacBookPro17,1","AppleARM">
          "config-number" = <000000000000000000000000000000000000000000000000000000$
          "IOPlatformSerialNumber" = "A01BC3QFQ05D"
          "regulatory-model-number" = <41323333380000000000000000000000000000000000$
          "time-stamp" = <"Mon Jun 27 20:12:10 PDT 2022">
          "clock-frequency" = <00366e01>
          "model" = <"MacBookPro17,1">
          "mlb-serial-number" = <432123413230363030455151384c4c314a0000000000000000$
          "model-number" = <4d59443832000000000000000000000000000000000000000000000$
          "IONWInterrupts" = "IONWInterrupts"
          "model-config" = <"SUNWAY;MoPED=0x803914B08BE6C5AF0E6C990D7D8240DA4CAC2FF$
          "device_type" = <"bootrom">
          "#size-cells" = <02000000>
          "IOPlatformUUID" = "1AB2345C-03E4-57D4-A375-1234D48DE123"
        }
END;
        $hostId = Detectors\Host::parseMacOsId($out);
        $this->assertIsString($hostId);
        $this->assertSame('1AB2345C-03E4-57D4-A375-1234D48DE123', $hostId);
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
    public function test_host_id_filesystem(string $os, array $files, string $expectedId): void
    {
        $root = vfsStream::setup('/', null, $files);
        $resouceDetector = new Detectors\Host($root->url(), $os);
        $resource = $resouceDetector->getResource();
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
            ['Linux', [], ''],
            ['Linux', $etc_machineid, '1234567890'],
            ['Linux', array_merge($etc_machineid, $varLibDbus), '1234567890'],
            ['Linux', $etc_machineid, '1234567890'],
            ['OpenBSD', [], ''],
            ['OpenBSD', $etc_hostid, '1234567890'],
        ];
    }
}
