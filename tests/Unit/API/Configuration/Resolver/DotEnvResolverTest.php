<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\API\Configuration\Resolver;

use OpenTelemetry\API\Configuration\Resolver\DotEnvResolver;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \OpenTelemetry\API\Configuration\Resolver\DotEnvResolver
 * @psalm-suppress InternalClass
 * @psalm-suppress InternalMethod
 */
class DotEnvResolverTest extends TestCase
{
    private vfsStreamDirectory $root;

    public function setUp(): void
    {
        $content = <<<EOS
#comment
FOO=foo #comment
BAR = bar
BAZ #invalid
BAT=key1=value1,key2=value2
#comment
EOS;

        $this->root = vfsStream::setup('root', null, ['.env' => $content]);
    }

    /**
     * @dataProvider variablesProvider
     */
    public function test_dotenv_resolver(string $key, ?string $value, $expected): void
    {
        $resolver = new DotEnvResolver($this->root->url());
        $this->assertSame($expected, $resolver->hasVariable($key));
        if ($expected) {
            $this->assertSame($value, $resolver->retrieveValue($key));
        }
    }

    public static function variablesProvider(): array
    {
        return [
            ['FOO', 'foo', true],
            ['BAR', 'bar', true],
            ['MISSING', null, false],
            ['BAT', 'key1=value1,key2=value2', true],
        ];
    }
}
