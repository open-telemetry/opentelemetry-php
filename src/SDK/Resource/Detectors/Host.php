<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use MachineIdSource;
use function php_uname;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.8.0/specification/resource/semantic_conventions/host.md#host
 */
final class Host implements ResourceDetectorInterface
{
    private string $dir;

    public function __construct(string $dir = '/')
    {
        $this->dir = $dir;
    }

    public function getResource(): ResourceInfo
    {
        $attributes = [
            ResourceAttributes::HOST_NAME => php_uname('n'),
            ResourceAttributes::HOST_ARCH => php_uname('m'),
            ResourceAttributes::HOST_ID => $this->getMachineId(),
        ];

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }

    private function getMachineId()
    {
        switch (strtolower(PHP_OS_FAMILY))
        {
            case 'linux':
            {
                return $this->getLinuxId();
            }
            case 'freebsd':
            case 'netbsd':
            case 'openbsd':
            {
                return $this->getBsdId();
            }
            case 'darwin':
            {
                $out = $this->getMacOsId();
                return Host::parseMacOsId($out);
            }
            case 'windows':
            {
                $out = $this->getWindowsId();
                return Host::parseWindowsId($out);
            }
        }

        return '';
    }

    private function getLinuxId(): string
    {
        $paths = ['etc/machine-id', 'var/lib/dbus/machine-id'];

        foreach ($paths as $path)
        {
            if (file_exists($this->dir . $path))
            {
                return trim(file_get_contents($this->dir . $path));
            }
        }

        return '';
    }

    private function getBsdId(): string
    {
        if (file_exists('/etc/hostid'))
        {
            return trim(file_get_contents('/etc/hostid'));
        }

        $out = exec('kenv -q smbios.system.uuid');

        if ($out != FALSE)
        {
            return $out;
        }

        return '';
    }

    private function getMacOsId(): string
    {
        $out = exec('ioreg -rd1 -c "IOPlatformExpertDevice"');

        if ($out != FALSE)
        {
            return $out;
        }

        return '';
    }

    private function getWindowsId(): string
    {
        $out = exec('%windir%\System32\REG.exe QUERY HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Cryptography /v MachineGuid');

        if ($out != FALSE)
        {
            return $out;
        }

        return '';
    }

    public static function parseMacOsId(string $out): string
    {
        $lines = explode("\n", $out);

        foreach ($lines as $line)
        {
            if (strpos($line, 'IOPlatformUUID') !== FALSE)
            {
                $parts = explode('=', $line);
                return trim($parts[1]);
            }
        }

        return '';
    }

    public static function parseWindowsId(string $out): string
    {
        $parts = explode('REG_SZ', $out);
        return trim($parts[1]);
    }
}
