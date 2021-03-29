<?php

declare(strict_types=1);

namespace OpenTelemetry\Context;

class Scope
{
    /** @var callable Context token, result of Context::attach() */
    private $contextToken;

    public function __construct(callable $contextToken)
    {
        $this->contextToken = $contextToken;
    }

    public function close(): void
    {
        Context::detach($this->contextToken);
    }
}
