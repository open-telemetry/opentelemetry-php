<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use function php_uname;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.8.0/specification/resource/semantic_conventions/host.md#host
 */
final class Host implements ResourceDetectorInterface
{
    private const PATH_ETC_MACHINEID = 'etc/machine-id';
    private const PATH_VAR_LIB_DBUS_MACHINEID = 'var/lib/dbus/machine-id';
    private const PATH_ETC_HOSTID = 'etc/hostid';
    private readonly string $dir;
    private readonly string $os;

    public function __construct(string $dir = '/', string $os = PHP_OS_FAMILY)
    {
        $this->dir = $dir;
        $this->os = $os;
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

    private function getMachineId(): ?string
    {
        switch ($this->os) {
            case 'Linux':
                {
                    return $this->getLinuxId();
                }
            case 'BSD':
                {
                    return $this->getBsdId();
                }
            case 'Darwin':
                {
                    return $this->getMacOsId();
                }
            case 'Windows':
                {
                    return $this->getWindowsId();
                }
        }

        return null;
    }

    private function getLinuxId(): ?string
    {
        $paths = [self::PATH_ETC_MACHINEID, self::PATH_VAR_LIB_DBUS_MACHINEID];

        foreach ($paths as $path) {
            if (file_exists($this->dir . $path)) {
                return trim(file_get_contents($this->dir . $path));
            }
        }

        return null;
    }

    private function getBsdId(): ?string
    {
        if (file_exists($this->dir . self::PATH_ETC_HOSTID)) {
            return trim(file_get_contents($this->dir . self::PATH_ETC_HOSTID));
        }

        $out = exec('kenv -q smbios.system.uuid');

        if ($out != false) {
            return $out;
        }

        return null;
    }

    private function getMacOsId(): ?string
    {
        $out = exec('ioreg -rd1 -c IOPlatformExpertDevice | awk \'/IOPlatformUUID/ { split($0, line, "\""); printf("%s\n", line[4]); }\'');

        if ($out != false) {
            return $out;
        }

        return null;
    }

    private function getWindowsId(): ?string
    {
        $out = exec('powershell.exe -Command "Get-ItemPropertyValue -Path HKLM:\SOFTWARE\Microsoft\Cryptography -Name MachineGuid"');

        if ($out != false) {
            return $out;
        }

        return null;
    }
}
