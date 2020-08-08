<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface Metric
{
    /**
     * Returns metric's name
     *
     * @access	public
     * @return	string
     */
    public function getName(): string;

    /**
     * Returns metric's description
     *
     * @access	public
     * @return	string
     */
    public function getDescription(): string;

    /**
     * Returns metric's type
     *
     * @access	public
     * @return	int
     */
    public function getType(): int;
}
