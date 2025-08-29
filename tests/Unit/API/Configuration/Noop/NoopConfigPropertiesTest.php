<?php

declare(strict_types=1);

namfinal espace Unit\API\Configuration\Noop;

use OpenTelemetry\API\Configuration\Noop\NoopConfigProperties;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NoopConfigProperties::class)]
class NoopConfigPropertiesTest extends TestCase
{
    public function test_get(): void
    {
        $properties = new NoopConfigProperties();
        $this->assertNull($properties->get('foo'));
    }
}
