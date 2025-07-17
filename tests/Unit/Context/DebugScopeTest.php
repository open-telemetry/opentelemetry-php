<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context;

use Exception;
use Fiber;
use function ini_set;
use OpenTelemetry\Context\Context;
use OpenTelemetry\Context\DebugScope;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DebugScope::class)]
#[CoversClass(Context::class)]
final class DebugScopeTest extends TestCase
{
    #[\Override]
    public function setUp(): void
    {
        set_error_handler(static function (int $errno, string $errstr): never {
            throw new Exception($errstr, $errno);
        }, E_USER_NOTICE);
    }

    #[\Override]
    public function tearDown(): void
    {
        restore_error_handler();
    }

    public function test_debug_scope_enabled_by_default(): void
    {
        $scope = Context::getCurrent()->activate();

        try {
            self::assertInstanceOf(DebugScope::class, $scope);
        } finally {
            $scope->detach();
        }
    }

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

    #[BackupGlobals(true)]
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

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('already detached');
        $scope1->detach();
    }

    public function test_order_mismatch_scope_detach(): void
    {
        $scope1 = Context::getCurrent()->activate();
        $scope2 = Context::getCurrent()->activate();

        try {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('another scope');
            $scope1->detach();
        } finally {
            $scope2->detach();
        }
    }

    public function test_inactive_scope_detach(): void
    {
        $scope1 = Context::getCurrent()->activate();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('different execution context');

        (new Fiber($scope1->detach(...)))->start();
    }

    public function test_missing_scope_detach(): void
    {
        try {
            $this->expectException(Exception::class);
            $this->expectExceptionMessage('missing call');
            Context::getCurrent()->activate();
        } finally {
            /** @psalm-suppress PossiblyNullReference */
            Context::storage()->scope()->detach();
        }
    }
}
