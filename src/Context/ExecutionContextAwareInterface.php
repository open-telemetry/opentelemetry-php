<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

interface ExecutionContextAwareInterface
{
    /**
     * @param int|string $id
     */
    public function fork($id): void;

    /**
     * @param int|string $id
     */
    public function switch($id): void;

    /**
     * @param int|string $id
     */
    public function destroy($id): void;
}
