<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for os.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/os/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface OsIncubatingAttributes
{
    /**
     * Unique identifier for a particular build or compilation of the operating system.
     *
     * @experimental
     */
    public const OS_BUILD_ID = 'os.build_id';

    /**
     * Human readable (not intended to be parsed) OS version information, like e.g. reported by `ver` or `lsb_release -a` commands.
     *
     * @experimental
     */
    public const OS_DESCRIPTION = 'os.description';

    /**
     * Human readable operating system name.
     *
     * @experimental
     */
    public const OS_NAME = 'os.name';

    /**
     * The operating system type.
     *
     * @experimental
     */
    public const OS_TYPE = 'os.type';

    /**
     * Microsoft Windows
     * @experimental
     */
    public const OS_TYPE_VALUE_WINDOWS = 'windows';

    /**
     * Linux
     * @experimental
     */
    public const OS_TYPE_VALUE_LINUX = 'linux';

    /**
     * Apple Darwin
     * @experimental
     */
    public const OS_TYPE_VALUE_DARWIN = 'darwin';

    /**
     * FreeBSD
     * @experimental
     */
    public const OS_TYPE_VALUE_FREEBSD = 'freebsd';

    /**
     * NetBSD
     * @experimental
     */
    public const OS_TYPE_VALUE_NETBSD = 'netbsd';

    /**
     * OpenBSD
     * @experimental
     */
    public const OS_TYPE_VALUE_OPENBSD = 'openbsd';

    /**
     * DragonFly BSD
     * @experimental
     */
    public const OS_TYPE_VALUE_DRAGONFLYBSD = 'dragonflybsd';

    /**
     * HP-UX (Hewlett Packard Unix)
     * @experimental
     */
    public const OS_TYPE_VALUE_HPUX = 'hpux';

    /**
     * AIX (Advanced Interactive eXecutive)
     * @experimental
     */
    public const OS_TYPE_VALUE_AIX = 'aix';

    /**
     * SunOS, Oracle Solaris
     * @experimental
     */
    public const OS_TYPE_VALUE_SOLARIS = 'solaris';

    /**
     * IBM z/OS
     * @experimental
     */
    public const OS_TYPE_VALUE_ZOS = 'zos';

    /**
     * The version string of the operating system as defined in [Version Attributes](/docs/resource/README.md#version-attributes).
     *
     * @experimental
     */
    public const OS_VERSION = 'os.version';

}
