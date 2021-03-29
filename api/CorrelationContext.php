<?php

declare(strict_types=1);

namespace OpenTelemetry;

use OpenTelemetry\Context\ContextKey;

interface CorrelationContext
{
    public function getCorrelations(); // TODO

    public function get(ContextKey $key);

    public static function getValue(ContextKey $key, ?CorrelationContext $ctx);

    public function set(ContextKey $key, $value): CorrelationContext;

    public static function setValue(ContextKey $key, $value, ?CorrelationContext $parent = null): CorrelationContext;

    public function removeCorrelation(): CorrelationContext;

    public function clearCorrelations(); // TODO
}
