<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

interface MetricInterface
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

    /**
     * Returns metric's resource
     *
     * @access	public
     * @return	ResourceInfo
     */
    public function getResource(): ResourceInfo;

    /**
     * Returns metric's instrumentation scope
     *
     * @access	public
     * @return	InstrumentationScopeInterface
     */
    public function getInstrumentationScope(): InstrumentationScopeInterface;

    /**
     * Returns metric's start epoch nanos
     *
     * @access	public
     * @return	int
     */
    public function getStartEpochNanos(): int;

    /**
     * Returns metric's epoch nanos
     *
     * @access	public
     * @return	int
     */
    public function getEpochNanos(): int;
}
