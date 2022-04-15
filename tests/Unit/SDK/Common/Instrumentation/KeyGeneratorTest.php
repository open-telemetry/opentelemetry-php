<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Instrumentation;

use OpenTelemetry\SDK\Common\Instrumentation\KeyGenerator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Instrumentation\KeyGenerator
 */
class KeyGeneratorTest extends TestCase
{
    public function test_non_null(): void
    {
        $name = 'foo';
        $version = 'bar';
        $schemaUrl = 'https://baz';

        $key = KeyGenerator::generateInstanceKey($name, $version, $schemaUrl);

        $this->assertSame(sprintf('%s@%s %s', $name, $version, $schemaUrl), $key);
    }

    public function test_null_version_and_null_schema(): void
    {
        $name = 'foo';

        $key = KeyGenerator::generateInstanceKey($name, null, null);

        $this->assertSame(sprintf('%s@unknown ', $name), $key);
    }

    public function test_non_null_version_and_null_schema(): void
    {
        $name = 'foo';
        $version = 'bar';

        $key = KeyGenerator::generateInstanceKey($name, $version, null);

        $this->assertSame(sprintf('%s@%s ', $name, $version), $key);
    }
}
