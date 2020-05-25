<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface UpDownCounter
{
    public function Add() : int;
}