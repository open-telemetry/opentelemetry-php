<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler;

class ConsistentSamplerTest
{
private static class Input
{
    private static string $traceId = "00112233445566778800000000000000";
    private static string $spanId = "0123456789abcdef";
    private static string $name = "name";
    private static SpanKind $spanKind = SpanKind::SERVER;
    private static Attributes $attributes;
    private static array $parentLinks = [];

    private bool $parentSampled = true;
    private ?int $parentThreshold = null;
    private ?int $parentRandomValue = null;

    public function __construct()
    {
        self::$attributes = Attributes::empty();
    }

    public function setParentSampled(bool $parentSampled): void
    {
        $this->parentSampled = $parentSampled;
    }

    public function setParentThreshold(int $parentThreshold): void
    {
        if ($parentThreshold < 0 || $parentThreshold > 0xffffffffffffff) {
            throw new \OutOfRangeException("parentThreshold must be between 0 and 0xffffffffffffff");
        }
        $this->parentThreshold = $parentThreshold;
    }

    public function setParentRandomValue(int $parentRandomValue): void
    {
        if ($parentRandomValue < 0 || $parentRandomValue > 0xffffffffffffff) {
            throw new \OutOfRangeException("parentRandomValue must be between 0 and 0xffffffffffffff");
        }
        $this->parentRandomValue = $parentRandomValue;
    }

    public function getParentContext(): Context
    {
        return $this->createParentContext(
            self::$traceId,
            self::$spanId,
            $this->parentThreshold,
            $this->parentRandomValue,
            $this->parentSampled
        );
    }

    public static function getTraceId(): string
    {
        return self::$traceId;
    }

    public static function getName(): string
    {
        return self::$name;
    }

    public static function getSpanKind(): SpanKind
    {
        return self::$spanKind;
    }

    public static function getAttributes(): Attributes
    {
        return self::$attributes;
    }

    public static function getParentLinks(): array
    {
        return self::$parentLinks;
    }

    private function createParentContext(
        string $traceId,
        string $spanId,
        ?int $parentThreshold,
        ?int $parentRandomValue,
        bool $parentSampled
    ): Context {
        // Implementation would go here - assuming this method exists elsewhere
        // or needs to be implemented based on the actual Context class
        throw new \RuntimeException("createParentContext method needs to be implemented");
    }
}
