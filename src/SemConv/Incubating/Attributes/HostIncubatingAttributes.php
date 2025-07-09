<?php

// DO NOT EDIT, this is an Auto-generated file from script/semantic-conventions

declare(strict_types=1);

namespace OpenTelemetry\SemConv\Incubating\Attributes;

/**
 * Semantic attributes and corresponding values for host.
 * @see https://opentelemetry.io/docs/specs/semconv/registry/attributes/host/
 * May contain @experimental Semantic Conventions which may change or be removed in the future.
 */
interface HostIncubatingAttributes
{
    /**
     * The CPU architecture the host system is running on.
     *
     * @experimental
     */
    public const HOST_ARCH = 'host.arch';

    /**
     * AMD64
     * @experimental
     */
    public const HOST_ARCH_VALUE_AMD64 = 'amd64';

    /**
     * ARM32
     * @experimental
     */
    public const HOST_ARCH_VALUE_ARM32 = 'arm32';

    /**
     * ARM64
     * @experimental
     */
    public const HOST_ARCH_VALUE_ARM64 = 'arm64';

    /**
     * Itanium
     * @experimental
     */
    public const HOST_ARCH_VALUE_IA64 = 'ia64';

    /**
     * 32-bit PowerPC
     * @experimental
     */
    public const HOST_ARCH_VALUE_PPC32 = 'ppc32';

    /**
     * 64-bit PowerPC
     * @experimental
     */
    public const HOST_ARCH_VALUE_PPC64 = 'ppc64';

    /**
     * IBM z/Architecture
     * @experimental
     */
    public const HOST_ARCH_VALUE_S390X = 's390x';

    /**
     * 32-bit x86
     * @experimental
     */
    public const HOST_ARCH_VALUE_X86 = 'x86';

    /**
     * The amount of level 2 memory cache available to the processor (in Bytes).
     *
     * @experimental
     */
    public const HOST_CPU_CACHE_L2_SIZE = 'host.cpu.cache.l2.size';

    /**
     * Family or generation of the CPU.
     *
     * @experimental
     */
    public const HOST_CPU_FAMILY = 'host.cpu.family';

    /**
     * Model identifier. It provides more granular information about the CPU, distinguishing it from other CPUs within the same family.
     *
     * @experimental
     */
    public const HOST_CPU_MODEL_ID = 'host.cpu.model.id';

    /**
     * Model designation of the processor.
     *
     * @experimental
     */
    public const HOST_CPU_MODEL_NAME = 'host.cpu.model.name';

    /**
     * Stepping or core revisions.
     *
     * @experimental
     */
    public const HOST_CPU_STEPPING = 'host.cpu.stepping';

    /**
     * Processor manufacturer identifier. A maximum 12-character string.
     *
     * [CPUID](https://wiki.osdev.org/CPUID) command returns the vendor ID string in EBX, EDX and ECX registers. Writing these to memory in this order results in a 12-character string.
     *
     * @experimental
     */
    public const HOST_CPU_VENDOR_ID = 'host.cpu.vendor.id';

    /**
     * Unique host ID. For Cloud, this must be the instance_id assigned by the cloud provider. For non-containerized systems, this should be the `machine-id`. See the table below for the sources to use to determine the `machine-id` based on operating system.
     *
     * @experimental
     */
    public const HOST_ID = 'host.id';

    /**
     * VM image ID or host OS image ID. For Cloud, this value is from the provider.
     *
     * @experimental
     */
    public const HOST_IMAGE_ID = 'host.image.id';

    /**
     * Name of the VM image or OS install the host was instantiated from.
     *
     * @experimental
     */
    public const HOST_IMAGE_NAME = 'host.image.name';

    /**
     * The version string of the VM image or host OS as defined in [Version Attributes](/docs/resource/README.md#version-attributes).
     *
     * @experimental
     */
    public const HOST_IMAGE_VERSION = 'host.image.version';

    /**
     * Available IP addresses of the host, excluding loopback interfaces.
     *
     * IPv4 Addresses MUST be specified in dotted-quad notation. IPv6 addresses MUST be specified in the [RFC 5952](https://www.rfc-editor.org/rfc/rfc5952.html) format.
     *
     * @experimental
     */
    public const HOST_IP = 'host.ip';

    /**
     * Available MAC addresses of the host, excluding loopback interfaces.
     *
     * MAC Addresses MUST be represented in [IEEE RA hexadecimal form](https://standards.ieee.org/wp-content/uploads/import/documents/tutorials/eui.pdf): as hyphen-separated octets in uppercase hexadecimal form from most to least significant.
     *
     * @experimental
     */
    public const HOST_MAC = 'host.mac';

    /**
     * Name of the host. On Unix systems, it may contain what the hostname command returns, or the fully qualified hostname, or another name specified by the user.
     *
     * @experimental
     */
    public const HOST_NAME = 'host.name';

    /**
     * Type of host. For Cloud, this must be the machine type.
     *
     * @experimental
     */
    public const HOST_TYPE = 'host.type';

}
