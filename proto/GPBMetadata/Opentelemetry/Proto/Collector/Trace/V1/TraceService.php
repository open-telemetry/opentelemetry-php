<?php

declare(strict_types=1);
// Generated by the protocol buffer compiler.  DO NOT EDIT!
// source: opentelemetry/proto/collector/trace/v1/trace_service.proto

namespace GPBMetadata\Opentelemetry\Proto\Collector\Trace\V1;

class TraceService
{
    public static $is_initialized = false;

    public static function initOnce()
    {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();

        if (static::$is_initialized == true) {
            return;
        }
        \GPBMetadata\Opentelemetry\Proto\Trace\V1\Trace::initOnce();
        $pool->internalAddGeneratedFile(hex2bin(
            '0a9d040a3a6f70656e74656c656d657472792f70726f746f2f636f6c6c65' .
            '63746f722f74726163652f76312f74726163655f736572766963652e7072' .
            '6f746f12266f70656e74656c656d657472792e70726f746f2e636f6c6c65' .
            '63746f722e74726163652e763122600a194578706f727454726163655365' .
            '72766963655265717565737412430a0e7265736f757263655f7370616e73' .
            '18012003280b322b2e6f70656e74656c656d657472792e70726f746f2e74' .
            '726163652e76312e5265736f757263655370616e73221c0a1a4578706f72' .
            '74547261636553657276696365526573706f6e736532a2010a0c54726163' .
            '65536572766963651291010a064578706f727412412e6f70656e74656c65' .
            '6d657472792e70726f746f2e636f6c6c6563746f722e74726163652e7631' .
            '2e4578706f7274547261636553657276696365526571756573741a422e6f' .
            '70656e74656c656d657472792e70726f746f2e636f6c6c6563746f722e74' .
            '726163652e76312e4578706f727454726163655365727669636552657370' .
            '6f6e736522004289010a29696f2e6f70656e74656c656d657472792e7072' .
            '6f746f2e636f6c6c6563746f722e74726163652e76314211547261636553' .
            '65727669636550726f746f50015a476769746875622e636f6d2f6f70656e' .
            '2d74656c656d657472792f6f70656e74656c656d657472792d70726f746f' .
            '2f67656e2f676f2f636f6c6c6563746f722f74726163652f763162067072' .
            '6f746f33'
        ));

        static::$is_initialized = true;
    }
}
