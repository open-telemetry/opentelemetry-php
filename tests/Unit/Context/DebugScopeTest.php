<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Context;

use OpenTelemetry\Context\Context;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\Context\DebugScope
 */
final class DebugScopeTest extends TestCase
{
    public function test_detached_scope_detach(): void
    {
        $scope1 = Context::getCurrent()->activate();

        $scope1->detach();

        $this->expectNotice();
        $this->expectNoticeMessage('already detached');
        $scope1->detach();
    }

    public function test_order_mismatch_scope_detach(): void
    {
        $scope1 = Context::getCurrent()->activate();
        $scope2 = Context::getCurrent()->activate();

        try {
            $this->expectNotice();
            $this->expectNoticeMessage('another scope');
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
            $this->expectNotice();
            $this->expectNoticeMessage('different execution context');
            $scope1->detach();
        } finally {
            Context::storage()->switch(0);
            Context::storage()->destroy(1);
        }
    }

    public function test_missing_scope_detach(): void
    {
        try {
            $this->expectNotice();
            $this->expectNoticeMessage('missing call');
            Context::getCurrent()->activate();
        } finally {
            /** @psalm-suppress PossiblyNullReference */
            Context::storage()->scope()->detach();
        }
    }
}
