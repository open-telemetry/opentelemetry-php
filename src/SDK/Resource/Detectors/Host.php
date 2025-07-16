<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\ResourceAttributes;
use const PHP_OS;
use const PHP_OS_FAMILY;
use function php_uname;
use function strtolower;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.8.0/specification/resource/semantic_conventions/host.md#host
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.8.0/specification/resource/semantic_conventions/os.md
 */
final class Host implements ResourceDetectorInterface
{
    private const PATH_ETC_MACHINEID = 'etc/machine-id';
    private const PATH_VAR_LIB_DBUS_MACHINEID = 'var/lib/dbus/machine-id';
    private const PATH_ETC_HOSTID = 'etc/hostid';

    public function __construct(
        private readonly string $dir = '/',
        private readonly string $os = PHP_OS_FAMILY,
    ) {
    }

    #[\Override]
    public function getResource(): ResourceInfo
    {
        $attributes = [
            ResourceAttributes::HOST_NAME => php_uname('n'),
            ResourceAttributes::HOST_ARCH => php_uname('m'),
            ResourceAttributes::HOST_ID => $this->getMachineId(),
            ResourceAttributes::OS_TYPE => strtolower(PHP_OS_FAMILY),
            ResourceAttributes::OS_DESCRIPTION => php_uname('r'),
            ResourceAttributes::OS_NAME => PHP_OS,
            ResourceAttributes::OS_VERSION => php_uname('v'),
        ];

        return ResourceInfo::create(Attributes::create($attributes), ResourceAttributes::SCHEMA_URL);
    }

    private function getMachineId(): ?string
    {
        return match ($this->os) {
            'Linux' => $this->getLinuxId(),
            'BSD' => $this->getBsdId(),
            'Darwin' => $this->getMacOsId(),
            'Windows' => $this->getWindowsId(),
            default => null,
        };
    }

    /**
     * @phan-suppress PhanTypeMismatchArgumentInternal
     */
    private function readFile(string $file): string|false
    {
        set_error_handler(static fn () => true);

        try {
            $contents = file_get_contents($file);

            return $contents !== false ? trim($contents) : false;
        } finally {
            restore_error_handler();
        }
    }

    private function getLinuxId(): ?string
    {
        $paths = [self::PATH_ETC_MACHINEID, self::PATH_VAR_LIB_DBUS_MACHINEID];

        foreach ($paths as $path) {
            $file = $this->dir . $path;

            $contents = $this->readFile($file);
            if ($contents !== false) {
                return $contents;
            }
        }

        return null;
    }

    private function getBsdId(): ?string
    {
        $file = $this->dir . self::PATH_ETC_HOSTID;

        $contents = $this->readFile($file);
        if ($contents !== false) {
            return $contents;
        }

        $out = exec('which kenv && kenv -q smbios.system.uuid');

        if ($out) {
            return $out;
        }

        return null;
    }

    private function getMacOsId(): ?string
    {
        $out = exec('ioreg -rd1 -c IOPlatformExpertDevice | awk \'/IOPlatformUUID/ { split($0, line, "\""); printf("%s\n", line[4]); }\'');

        if ($out !== false) {
            return $out;
        }

        return null;
    }

    private function getWindowsId(): ?string
    {
        $out = exec('powershell.exe -Command "Get-ItemPropertyValue -Path HKLM:\SOFTWARE\Microsoft\Cryptography -Name MachineGuid"');

        if ($out !== false) {
            return $out;
        }

        return null;
    }
}
