<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Common\Instrumentation;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationLibrary;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\SDK\Common\Instrumentation\InstrumentationLibrary
 */
class InstrumentationLibraryTest extends TestCase
{
    public function test_get_empty(): void
    {
        $library = InstrumentationLibrary::getEmpty();

        $this->assertEmpty($library->getName());
        $this->assertNull($library->getVersion());
        $this->assertNull($library->getSchemaUrl());
    }

    public function test_getters(): void
    {
        $name = 'foo';
        $version = 'bar';
        $schemaUrl = 'http://baz';

        $library = new InstrumentationLibrary($name, $version, $schemaUrl);

        $this->assertSame($name, $library->getName());
        $this->assertSame($version, $library->getVersion());
        $this->assertSame($schemaUrl, $library->getSchemaUrl());
    }
}
