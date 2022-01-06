<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK;

use OpenTelemetry\SDK\InstrumentationLibrary;
use PHPUnit\Framework\TestCase;

/**
 * @covers OpenTelemetry\SDK\InstrumentationLibrary
 */
class InstrumentationLibraryTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState false
     */
    public function test_get_empty(): void
    {
        $library = InstrumentationLibrary::getEmpty();
        $this->assertSame($library, InstrumentationLibrary::getEmpty());
    }

    public function test_getters(): void
    {
        $library = new InstrumentationLibrary('name', 'version');
        $this->assertSame('name', $library->getName());
        $this->assertSame('version', $library->getVersion());
    }
}
