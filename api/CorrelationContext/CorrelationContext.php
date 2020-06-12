<?php

declare(strict_types=1);

namespace OpenTelemetry\CorrelationContext;

use OpenTelemetry\Context\Context;

interface CorrelationContext
{
    public function getCorrelations(); // TODO
    public function get(ContextKey $key);
    public static function getValue(ContextKey $key, ?Context $ctx);
    public function set(ContextKey $key, $value): Context;
    public static function setValue(ContextKey $key, $value, ?Context $parent=null): Context;
    public function removeCorrelation(): Context;
    public function clearCorrelations(); // TODO
}
