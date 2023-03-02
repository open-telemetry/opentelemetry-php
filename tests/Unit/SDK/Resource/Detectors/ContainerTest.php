<?php

declare(strict_types=1);

namespace OpenTelemetry\Example\Unit\SDK\Resource\Detectors;

use OpenTelemetry\SDK\Resource\Detectors\Container;
use OpenTelemetry\SemConv\ResourceAttributes;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\Resource\Detectors\Container
 */
class ContainerTest extends TestCase
{
    private vfsStreamFile $cgroup;
    private vfsStreamFile $mountinfo;
    private Container $detector;

    public function setUp(): void
    {
        $root = vfsStream::setup();
        $this->cgroup = vfsStream::newFile('cgroup')->at($root);
        $this->mountinfo = vfsStream::newFile('mountinfo')->at($root);
        $this->detector = new Container($root->url());
    }

    public function test_valid_v1(): void
    {
        $valid = 'a8493b8a4f6f23b65c5db50be86619ca4da078da040aa3d5ccff26fe50de205d';
        $this->cgroup->setContent($valid);
        $resource = $this->detector->getResource();

        $this->assertSame(ResourceAttributes::SCHEMA_URL, $resource->getSchemaUrl());
        $this->assertIsString($resource->getAttributes()->get(ResourceAttributes::CONTAINER_ID));
        $this->assertSame($valid, $resource->getAttributes()->get(ResourceAttributes::CONTAINER_ID));
    }

    public function test_invalid_v1(): void
    {
        $this->cgroup->setContent('0::/');
        $resource = $this->detector->getResource();

        $this->assertEmpty($resource->getAttributes());
    }

    public function test_valid_v2(): void
    {
        $expected = 'a8493b8a4f6f23b65c5db50be86619ca4da078da040aa3d5ccff26fe50de205d';
        $data = <<< EOS
1366 1365 0:30 / /sys/fs/cgroup ro,nosuid,nodev,noexec,relatime - cgroup2 cgroup rw
1408 1362 0:107 / /dev/mqueue rw,nosuid,nodev,noexec,relatime - mqueue mqueue rw
1579 1362 0:112 / /dev/shm rw,nosuid,nodev,noexec,relatime - tmpfs shm rw,size=65536k,inode64
1581 1359 259:2 /var/lib/docker/containers/a8493b8a4f6f23b65c5db50be86619ca4da078da040aa3d5ccff26fe50de205d/hostname /etc/hostname rw,relatime - ext4 /dev/nvme0n1p2 rw,errors=remount-ro
1583 1359 259:3 /brett/docker/otel/opentelemetry-php /usr/src/myapp rw,relatime - ext4 /dev/nvme0n1p3 rw
EOS;
        $this->mountinfo->withContent($data);
        $resource = $this->detector->getResource();

        $this->assertCount(1, $resource->getAttributes());
        $this->assertSame($expected, $resource->getAttributes()->get(ResourceAttributes::CONTAINER_ID));
    }

    public function test_invalid_v2(): void
    {
        $data = <<< EOS
1581 1359 259:2 /var/lib/docker/containers/a8493b8a4f6f23b65c5db50be86619ca4da078da040aa3d5ccff26fe50de205d/wrongkeyword
EOS;
        $this->mountinfo->withContent($data);
        $resource = $this->detector->getResource();

        $this->assertEmpty($resource->getAttributes());
    }
}
