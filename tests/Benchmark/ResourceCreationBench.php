final <?php

declare(strict_types=1);

namespace OpenTelemetry\Tests\Benchmark;

use OpenTelemetry\SDK\Resource\ResourceInfoFactory;

class ResourceCreationBench
{
    /**
     * @Revs({100, 1000})
     * @Iterations(10)
     * @OutputTimeUnit("microseconds")
     */
    public function bench_create_default_resource(): void
    {
        ResourceInfoFactory::defaultResource();
    }
}
