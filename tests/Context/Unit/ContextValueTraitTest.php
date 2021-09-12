<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Context\Unit;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Context\ContextValueTrait;
use PHPUnit\Framework\TestCase;

class ContextValueTraitTest extends TestCase
{
    /**
     * @test
     */
    public function getContextKeyShouldBeCalledOnContextInteraction()
    {
        $ctxValue = new class() {
            use ContextValueTrait;

            private static $contextKey;

            public static $invocations = 0; // spy counter

            protected static function getContextKey(): ContextKey
            {
                self::$invocations++;

                if (self::$contextKey === null) {
                    self::$contextKey = new ContextKey('test-key');
                }

                return self::$contextKey;
            }
        };

        $ctxValue::getCurrent();
        $this->assertEquals(1, $ctxValue::$invocations);

        $ctxValue::setCurrent($ctxValue);
        $this->assertEquals(2, $ctxValue::$invocations);

        $context = new Context();
        $ctxValue::insert($ctxValue, $context);
        $this->assertEquals(3, $ctxValue::$invocations);

        $ctxValue::fromContext($context);
        $this->assertEquals(4, $ctxValue::$invocations);
    }
}
