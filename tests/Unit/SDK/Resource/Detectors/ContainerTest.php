<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors\Container;
use OpenTelemetry\SemConv\Attributes\ContainerAttributes;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Container::class)]
final class ContainerTest extends TestCase
{
    // A valid 64-character lowercase hex container ID (Docker / containerd format).
    private const CONTAINER_ID = 'a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4e5f6a1b2';

    // A cgroup v1 line that embeds the container ID in the third colon-separated field.
    private const CGROUP_LINE = '12:cpu:/docker/' . self::CONTAINER_ID;

    // -------------------------------------------------------------------------
    // No cgroup file → empty resource
    // -------------------------------------------------------------------------

    public function test_returns_empty_resource_when_cgroup_file_is_absent(): void
    {
        $root = vfsStream::setup('/');
        $detector = new Container($root->url() . '/proc/self/cgroup');

        $resource = $detector->getResource();

        $this->assertNull($resource->getAttributes()->get(ContainerAttributes::CONTAINER_ID));
    }

    // -------------------------------------------------------------------------
    // cgroup v1 happy path
    // -------------------------------------------------------------------------

    #[DataProvider('cgroupContentProvider')]
    public function test_extracts_container_id_from_cgroup_v1(string $cgroupContent): void
    {
        $root = vfsStream::setup('/', null, [
            'proc' => ['self' => ['cgroup' => $cgroupContent]],
        ]);
        $detector = new Container($root->url() . '/proc/self/cgroup');

        $resource = $detector->getResource();

        $this->assertSame(self::CONTAINER_ID, $resource->getAttributes()->get(ContainerAttributes::CONTAINER_ID));
        $this->assertStringMatchesFormat('https://opentelemetry.io/schemas/%d.%d.%d', $resource->getSchemaUrl() ?? '');
    }

    public static function cgroupContentProvider(): iterable
    {
        // Single-line cgroup v1
        yield 'single line' => [self::CGROUP_LINE];

        // Container ID on the last of several lines
        yield 'multi-line, id on last line' => [
            "0::/\n1:memory:/\n" . self::CGROUP_LINE,
        ];

        // Container ID on the first of several lines
        yield 'multi-line, id on first line' => [
            self::CGROUP_LINE . "\n0::/\n1:memory:/",
        ];
    }

    // -------------------------------------------------------------------------
    // cgroup file exists but contains no 64-char hex string → empty resource
    // -------------------------------------------------------------------------

    public function test_returns_empty_resource_when_no_container_id_in_cgroup(): void
    {
        $root = vfsStream::setup('/', null, [
            'proc' => ['self' => ['cgroup' => "0::/\n1:memory:/\n"]],
        ]);
        $detector = new Container($root->url() . '/proc/self/cgroup');

        $resource = $detector->getResource();

        $this->assertNull($resource->getAttributes()->get(ContainerAttributes::CONTAINER_ID));
    }

    // -------------------------------------------------------------------------
    // Unreadable cgroup file → empty resource (graceful degradation)
    // -------------------------------------------------------------------------

    public function test_returns_empty_resource_when_cgroup_file_is_unreadable(): void
    {
        $root = vfsStream::setup('/');
        $proc = vfsStream::newDirectory('proc/self', 0755)->at($root);
        vfsStream::newFile('cgroup', 0000)
            ->at($proc)
            ->setContent(self::CGROUP_LINE);

        $detector = new Container($root->url() . '/proc/self/cgroup');

        $resource = $detector->getResource();

        $this->assertNull($resource->getAttributes()->get(ContainerAttributes::CONTAINER_ID));
    }

    // -------------------------------------------------------------------------
    // Short hex string (< 64 chars) must not be treated as a container ID
    // -------------------------------------------------------------------------

    public function test_ignores_short_hex_strings(): void
    {
        $shortHex = str_repeat('a', 32); // only 32 chars, not 64
        $root = vfsStream::setup('/', null, [
            'proc' => ['self' => ['cgroup' => "12:cpu:/docker/{$shortHex}"]],
        ]);
        $detector = new Container($root->url() . '/proc/self/cgroup');

        $resource = $detector->getResource();

        $this->assertNull($resource->getAttributes()->get(ContainerAttributes::CONTAINER_ID));
    }
}
