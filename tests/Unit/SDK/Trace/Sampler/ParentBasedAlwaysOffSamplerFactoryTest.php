<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\SDK\Trace\Sampler;

use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\Sampler\ParentBasedAlwaysOffSamplerFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ParentBasedAlwaysOffSamplerFactory::class)]
class ParentBasedAlwaysOffSamplerFactoryTest extends TestCase
{
    public function test_create(): void
    {
        $factory = new ParentBasedAlwaysOffSamplerFactory();
        $sampler = $factory->create();

        $this->assertInstanceOf(ParentBased::class, $sampler);
        $this->assertStringContainsString('ParentBased', $sampler->getDescription());
        $this->assertStringContainsString('AlwaysOff', $sampler->getDescription());
    }
}
