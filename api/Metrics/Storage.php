<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface Storage
{
    /**
     * Returns Metric with the specified name or null if nothing has been found
     *
     * @access	public
     * @param	string	$name	
     * @return	Metric|null
     */
    public function get(string $name): ?Metric;

    /**
     * Returns iterable of metrics or null if nothing has been found
     *
     * @access	public
     * @param	array<string>	$names
     * @return	iterable<Mertic>
     */
    public function getMany(array $names): ?iterable;

    /**
     * Creates or updates metric or throws an exception if update failed
     *
     * @access	public
     * @throws  \OpenTelemetry\Sdk\Metrics\Exceptions\StorageException
     * @param	Metric	$metric
     * @return	void
     */
    public function save(Metric $metric): void;

    /**
     * Saves or updates several metrics at once
     *
     * @access	public
     * @throws  \OpenTelemetry\Sdk\Metrics\Exceptions\StorageException
     * @param	iterable<Metric>	$metrics
     * @return	void
     */
    public function saveMany(iterable $metrics): void;

    /**
     * Deletes the provided metric from storage
     *
     * @access	public
     * @throws  \OpenTelemetry\Sdk\Metrics\Exceptions\StorageException
     * @param	Metric|string	$metric
     * @return	void
     */
    public function delete($metric): void;

    /**
     * Deletes the provided metrics from storage
     *
     * @access	public
     * @throws  \OpenTelemetry\Sdk\Metrics\Exceptions\StorageException
     * @param	iterable<Metric|string>	$metrics
     * @return	void
     */
    public function deleteMany(iterable $metrics): void
}
