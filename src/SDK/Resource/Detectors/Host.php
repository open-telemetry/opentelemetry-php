<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceDetectorInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SemConv\Incubating\Attributes\HostIncubatingAttributes;
use OpenTelemetry\SemConv\Incubating\Attributes\OsIncubatingAttributes;
use OpenTelemetry\SemConv\Version;
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
            HostIncubatingAttributes::HOST_NAME => php_uname('n'),
            HostIncubatingAttributes::HOST_ARCH => php_uname('m'),
            HostIncubatingAttributes::HOST_ID => $this->getMachineId(),
            OsIncubatingAttributes::OS_TYPE => strtolower(PHP_OS_FAMILY),
            OsIncubatingAttributes::OS_DESCRIPTION => php_uname('r'),
            OsIncubatingAttributes::OS_NAME => PHP_OS,
            OsIncubatingAttributes::OS_VERSION => php_uname('v'),
        ];

        return ResourceInfo::create(Attributes::create($attributes), Version::VERSION_1_38_0->url());
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

        $out = $this->run(['/bin/kenv', '-q', 'smbios.system.uuid']);

        return $out !== null && $out !== '' ? $out : null;
    }

    private function getMacOsId(): ?string
    {
        $out = $this->run(['/usr/sbin/ioreg', '-rd1', '-c', 'IOPlatformExpertDevice']);

        if ($out !== null && preg_match('/"IOPlatformUUID" = "([^"]+)"/', $out, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function getWindowsId(): ?string
    {
        $reg = (getenv('SystemRoot') ?: 'C:\Windows') . '\System32\reg.exe';
        $out = $this->run([$reg, 'query', 'HKLM\SOFTWARE\Microsoft\Cryptography', '/v', 'MachineGuid', '/reg:64']);

        if ($out !== null && preg_match('/MachineGuid\s+REG_SZ\s+(\S+)/', $out, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * The first element must be an absolute path. An argument list avoids the shell, but
     * proc_open() still searches PATH for a bare name, and is_executable() checks a bare name
     * against the current directory instead, so it would not stop one.
     *
     * @param non-empty-list<string> $command
     */
    private function run(array $command): ?string
    {
        if (!function_exists('proc_open')) {
            return null;
        }

        set_error_handler(static fn () => true);

        try {
            if (!is_executable($command[0])) {
                return null;
            }
            $process = proc_open($command, [0 => ['null'], 1 => ['pipe', 'w'], 2 => ['null']], $pipes);
            if ($process === false) {
                return null;
            }
            $out = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            return proc_close($process) === 0 && $out !== false ? trim($out) : null;
        } finally {
            restore_error_handler();
        }
    }
}
