<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

use BadMethodCallException;

/**
 * @internal
 */
trait ExecutionContextAwareNotSupported
{

    public function fork(int|string $id): void
    {
        throw new BadMethodCallException();
    }

    public function switch(int|string $id): void
    {
        throw new BadMethodCallException();
    }

    public function destroy(int|string $id): void
    {
        throw new BadMethodCallException();
    }
}
