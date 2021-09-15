<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Context\Unit;

use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\ContextKey;
use OpenTelemetry\Context\Scope;
use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
    public function testScopeCloseRestoresContext(): void
    {
        $key = new ContextKey();
        $ctx = (new Context())->with($key, 'test');
        $scope = new Scope(Context::attach($ctx));

        $this->assertSame('test', Context::getValue($key));

        $scope->close();

        $this->assertNull(Context::getValue($key));
    }

    public function testNestedScope(): void
    {
        $key = new ContextKey();
        $ctx1 = (new Context())->with($key, 'test1');
        $scope1 = new Scope(Context::attach($ctx1));
        $this->assertSame('test1', Context::getValue($key));

        $ctx2 = (new Context())->with($key, 'test2');
        $scope2 = new Scope(Context::attach($ctx2));
        $this->assertSame('test2', Context::getValue($key));

        $scope2->close();
        $this->assertSame('test1', Context::getValue($key));

        $scope1->close();
        $this->assertNull(Context::getValue($key));
    }
}
