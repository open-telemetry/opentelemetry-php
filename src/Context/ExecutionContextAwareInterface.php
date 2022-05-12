<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

interface ExecutionContextAwareInterface
{
    public function fork(int $id): void;

    public function switch(int $id): void;

    public function destroy(int $id): void;
}
