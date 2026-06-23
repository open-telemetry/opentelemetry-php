<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy;

use OpenTelemetry\SDK\Trace\SpanSuppression\SemanticConventionSuppressionStrategy\WildcardPattern;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WildcardPattern::class)]
class WildcardPatternTest extends TestCase
{
    public function test_static_match(): void
    {
        $pattern = new WildcardPattern();
        $pattern->add('exact');
        $this->assertTrue($pattern->matches('exact'));
    }

    public function test_static_no_match(): void
    {
        $pattern = new WildcardPattern();
        $pattern->add('exact');
        $this->assertFalse($pattern->matches('other'));
    }

    public function test_wildcard_star(): void
    {
        $pattern = new WildcardPattern();
        $pattern->add('foo*');
        $this->assertTrue($pattern->matches('foobar'));
        $this->assertTrue($pattern->matches('foo'));
        $this->assertFalse($pattern->matches('barfoo'));
    }

    public function test_wildcard_question(): void
    {
        $pattern = new WildcardPattern();
        $pattern->add('fo?');
        $this->assertTrue($pattern->matches('foo'));
        $this->assertTrue($pattern->matches('fob'));
        $this->assertFalse($pattern->matches('fooo'));
    }

    public function test_wildcard_middle(): void
    {
        $pattern = new WildcardPattern();
        $pattern->add('foo*bar');
        $this->assertTrue($pattern->matches('foobar'));
        $this->assertTrue($pattern->matches('fooxyzbar'));
        $this->assertFalse($pattern->matches('foobarbaz'));
    }

    public function test_no_patterns_no_match(): void
    {
        $pattern = new WildcardPattern();
        $this->assertFalse($pattern->matches('anything'));
    }

    public function test_multiple_patterns(): void
    {
        $pattern = new WildcardPattern();
        $pattern->add('foo');
        $pattern->add('bar*');
        $this->assertTrue($pattern->matches('foo'));
        $this->assertTrue($pattern->matches('barbaz'));
        $this->assertFalse($pattern->matches('qux'));
    }
}
