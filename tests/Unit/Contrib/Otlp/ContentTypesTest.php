<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Unit\Contrib\Otlp;

use OpenTelemetry\Contrib\Otlp\ContentTypes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * ContentTypes is an interface with constants only.
 * This test verifies the constant values are correct.
 */
#[CoversClass(ContentTypes::class)]
final class ContentTypesTest extends TestCase
{
    public function test_protobuf_constant(): void
    {
        $this->assertSame('application/x-protobuf', ContentTypes::PROTOBUF);
    }

    public function test_json_constant(): void
    {
        $this->assertSame('application/json', ContentTypes::JSON);
    }

    public function test_ndjson_constant(): void
    {
        $this->assertSame('application/x-ndjson', ContentTypes::NDJSON);
    }
}
