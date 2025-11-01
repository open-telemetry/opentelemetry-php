<?php

declare(strict_types=1);

namespace API\Configuration;

use OpenTelemetry\API\Configuration\Context;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Context::class)]
final class ConfigurationContextTest extends TestCase
{
    public function test_context_missing_extensions_returns_null(): void
    {
        $context = new Context();

        $this->assertNull($context->getExtension(ResourceInfo::class));
    }

    public function test_context_extension_returns_assigned_value(): void
    {
        $context = new Context();
        $context = $context->withExtension(ResourceInfo::create(Attributes::create([
            'service.name' => 'test-service',
        ])));

        $this->assertSame('test-service', $context->getExtension(ResourceInfo::class)?->getAttributes()->get('service.name'));
    }
}
