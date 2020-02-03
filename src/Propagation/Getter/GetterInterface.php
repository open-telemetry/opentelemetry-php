<?php

declare(strict_types=1);

namespace OpenTelemetry\Propagation\Getter;

interface GetterInterface
{
    /**
     * Gets the value of a given key on a carrier.
     *
     * @param $carrier
     * @param string $key
     * @return string|null
     */
    public function get($carrier, string $key) : ?string;
}