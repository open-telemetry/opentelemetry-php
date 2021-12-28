<?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Contrib\Unit;

use OpenTelemetry\Contrib\Jaeger\AgentExporter;
use PHPUnit\Framework\TestCase;

class AgentExporterTest extends TestCase
{
    /**
     * @test
     */
    public function happyPath() 
    {
        $exporter = new AgentExporter(
            "serviceName", //This isn't realistic I imagine
            "http://127.0.0.1:80" //This isn't realistic I imagine
        );

        $this->expectNotToPerformAssertions();
    }
}