<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface Counter
{
    public function Add() : int;
}