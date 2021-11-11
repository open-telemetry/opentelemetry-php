<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Context\Unit;

use OpenTelemetry\Context\ContextKey;
use PHPUnit\Framework\TestCase;

class ContextKeyTest extends TestCase
{
    public function testName()
    {
        $name = 'foo';

        $this->assertSame(
            $name,
            (new ContextKey($name))
                ->name()
        );
    }
}
