<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Jaeger\BatchAdapter;

class BatchAdapterFactory implements BatchAdapterFactoryInterface
{
    public function create(array $values): BatchAdapterInterface
    {
        return new BatchAdapter($values);
    }
}
