<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// Copyright 2019, OpenTelemetry Authors
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//
namespace Opentelemetry\Proto\Metrics\Experimental;

/**
 * MetricConfig is a service that enables updating metric schedules, trace
 * parameters, and other configurations on the SDK without having to restart the
 * instrumented application. The collector can also serve as the configuration
 * service, acting as a bridge between third-party configuration services and
 * the SDK, piping updated configs from a third-party source to an instrumented
 * application.
 */
class MetricConfigClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Opentelemetry\Proto\Metrics\Experimental\MetricConfigRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function GetMetricConfig(\Opentelemetry\Proto\Metrics\Experimental\MetricConfigRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/opentelemetry.proto.metrics.experimental.MetricConfig/GetMetricConfig',
        $argument,
        ['\Opentelemetry\Proto\Metrics\Experimental\MetricConfigResponse', 'decode'],
        $metadata, $options);
    }

}
