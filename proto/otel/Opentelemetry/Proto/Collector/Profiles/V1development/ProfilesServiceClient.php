<?php
// GENERATED CODE -- DO NOT EDIT!

// Original file comments:
// Copyright 2023, OpenTelemetry Authors
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
namespace Opentelemetry\Proto\Collector\Profiles\V1development;

/**
 * Service that can be used to push profiles between one Application instrumented with
 * OpenTelemetry and a collector, or between a collector and a central collector.
 */
class ProfilesServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Opentelemetry\Proto\Collector\Profiles\V1development\ExportProfilesServiceRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function Export(\Opentelemetry\Proto\Collector\Profiles\V1development\ExportProfilesServiceRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/opentelemetry.proto.collector.profiles.v1development.ProfilesService/Export',
        $argument,
        ['\Opentelemetry\Proto\Collector\Profiles\V1development\ExportProfilesServiceResponse', 'decode'],
        $metadata, $options);
    }

}
