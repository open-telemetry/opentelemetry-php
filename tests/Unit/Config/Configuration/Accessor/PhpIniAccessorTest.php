<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Config\Configuration\Accessor;

use OpenTelemetry\Config\Configuration\Accessor\PhpIniAccessor;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Config\Configuration\Accessor\PhpIniAccessor
 */
class PhpIniAccessorTest extends TestCase
{
    public function test_access(): void
    {
        $accessor = new PhpIniAccessor();
        $value = $accessor->get('assert.active');

        /** @psalm-suppress RedundantCondition */
        $this->assertNotNull($value);
    }
}
