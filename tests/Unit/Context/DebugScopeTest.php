<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context;

use function ini_set;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\DebugScope;
use PHPUnit\Framework\Exception as PHPUnitFrameworkException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Context\DebugScope
 */
final class DebugScopeTest extends TestCase
{

    /**
     * @covers \OpenTelemetry\Context\Context::activate
     * @covers \OpenTelemetry\Context\Context::debugScopesDisabled
     */
    public function test_debug_scope_enabled_by_default(): void
    {
        $scope = Context::getCurrent()->activate();

        try {
            self::assertInstanceOf(DebugScope::class, $scope);
        } finally {
            $scope->detach();
        }
    }

    /**
     * @covers \OpenTelemetry\Context\Context::activate
     */
    public function test_disable_debug_scope_using_assertion_mode(): void
    {
        ini_set('zend.assertions', '0');
        $scope = Context::getCurrent()->activate();

        try {
            self::assertNotInstanceOf(DebugScope::class, $scope);
        } finally {
            ini_set('zend.assertions', '1');
            $scope->detach();
        }
    }

    /**
     * @covers \OpenTelemetry\Context\Context::activate
     * @covers \OpenTelemetry\Context\Context::debugScopesDisabled
     * @backupGlobals
     */
    public function test_disable_debug_scope_using_otel_php_debug_scopes_disabled(): void
    {
        $_SERVER['OTEL_PHP_DEBUG_SCOPES_DISABLED'] = 'true';
        $scope = Context::getCurrent()->activate();

        try {
            self::assertNotInstanceOf(DebugScope::class, $scope);
        } finally {
            $scope->detach();
        }
    }

    public function test_detached_scope_detach(): void
    {
        $scope1 = Context::getCurrent()->activate();

        $scope1->detach();

        $this->expectException(PHPUnitFrameworkException::class);
        $this->expectExceptionMessage('already detached');
        $scope1->detach();
    }

    public function test_order_mismatch_scope_detach(): void
    {
        $scope1 = Context::getCurrent()->activate();
        $scope2 = Context::getCurrent()->activate();

        try {
            $this->expectException(PHPUnitFrameworkException::class);
            $this->expectExceptionMessage('another scope');
            $scope1->detach();
        } finally {
            $scope2->detach();
        }
    }

    public function test_inactive_scope_detach(): void
    {
        $scope1 = Context::getCurrent()->activate();

        Context::storage()->fork(1);
        Context::storage()->switch(1);

        try {
            $this->expectException(PHPUnitFrameworkException::class);
            $this->expectExceptionMessage('different execution context');
            $scope1->detach();
        } finally {
            Context::storage()->switch(0);
            Context::storage()->destroy(1);
        }
    }

    public function test_missing_scope_detach(): void
    {
        try {
            $this->expectException(PHPUnitFrameworkException::class);
            $this->expectExceptionMessage('missing call');
            Context::getCurrent()->activate();
        } finally {
            /** @psalm-suppress PossiblyNullReference */
            Context::storage()->scope()->detach();
        }
    }
}
