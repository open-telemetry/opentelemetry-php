<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Integration\SDK\Resource;

use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Resource\Detectors;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\Tests\TestState;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ResourceInfoTest extends TestCase
{
    use TestState;

    /**
     * @dataProvider environmentResourceProvider
     * @group compliance
     */
    public function test_resource_from_environment(string $envAttributes, array $userAttributes, array $expected): void
    {
        $this->setEnvironmentVariable('OTEL_RESOURCE_ATTRIBUTES', $envAttributes);
        $resource = (new Detectors\Composite([
            new Detectors\Environment(),
            new Detectors\Constant(ResourceInfo::create(Attributes::create($userAttributes))),
        ]))->getResource();
        foreach ($expected as $name => $value) {
            $this->assertSame($value, $resource->getAttributes()->get($name));
        }
    }

    public static function environmentResourceProvider(): array
    {
        return [
            'attributes from env var' => [
                'foo=foo,bar=bar',
                [],
                ['foo' => 'foo', 'bar' => 'bar'],
            ],
            'user attributes have higher priority' => [
                'foo=env-foo,bar=env-bar,baz=env-baz',
                ['foo' => 'user-foo', 'bar' => 'user-bar'],
                ['foo' => 'user-foo', 'bar' => 'user-bar', 'baz' => 'env-baz'],
            ],
        ];
    }
}
