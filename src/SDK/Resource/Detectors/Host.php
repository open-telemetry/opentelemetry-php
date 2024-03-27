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

    private function getMachineId()
    {
        switch (strtolower($this->os)) {
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

                    return self::parseMacOsId($out);
                }
            case 'windows':
                {
                    $out = $this->getWindowsId();

                    return self::parseWindowsId($out);
                }
        }

        return '';
    }

    private function getLinuxId(): string
    {
        $paths = [self::PATH_ETC_MACHINEID, self::PATH_VAR_LIB_DBUS_MACHINEID];

        foreach ($paths as $path) {
            if (file_exists($this->dir . $path)) {
                return trim(file_get_contents($this->dir . $path));
            }
        }

        return '';
    }

    private function getBsdId(): string
    {
        if (file_exists($this->dir . self::PATH_ETC_HOSTID)) {
            return trim(file_get_contents($this->dir . self::PATH_ETC_HOSTID));
        }

        $out = exec('kenv -q smbios.system.uuid');

        if ($out != false) {
            return $out;
        }

        return '';
    }

    private function getMacOsId(): string
    {
        $out = exec('ioreg -rd1 -c "IOPlatformExpertDevice"');

        if ($out != false) {
            return $out;
        }

        return '';
    }

    private function getWindowsId(): string
    {
        $out = exec('%windir%\System32\REG.exe QUERY HKEY_LOCAL_MACHINE\SOFTWARE\Microsoft\Cryptography /v MachineGuid');

        if ($out != false) {
            return $out;
        }

        return '';
    }

    public static function parseMacOsId(string $out): string
    {
        $lines = explode(PHP_EOL, $out);

        foreach ($lines as $line) {
            if (str_contains($line, 'IOPlatformUUID')) {
                $parts = explode('=', $line);

                return trim(str_replace('"', '', $parts[1]));
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
