echo "Cloning opentelemetry-proto folder"
svn checkout https://github.com/open-telemetry/opentelemetry-proto/trunk/opentelemetry
mkdir proto
protoc --proto_path=./ --php_out=proto opentelemetry/proto/collector/logs/v1/logs_service.proto
protoc --proto_path=./ --php_out=proto opentelemetry/proto/collector/metrics/v1/metrics_service.proto
protoc --proto_path=./ --php_out=proto opentelemetry/proto/collector/trace/v1/trace_service.proto
protoc --proto_path=./ --php_out=proto opentelemetry/proto/common/v1/common.proto
protoc --proto_path=./ --php_out=proto opentelemetry/proto/logs/v1/logs.proto
protoc --proto_path=./ --php_out=proto opentelemetry/proto/metrics/experimental/configservice.proto
protoc --proto_path=./ --php_out=proto opentelemetry/proto/metrics/v1/metrics.proto
protoc --proto_path=./ --php_out=proto opentelemetry/proto/resource/v1/resource.proto
protoc --proto_path=./ --php_out=proto opentelemetry/proto/trace/v1/trace_config.proto
protoc --proto_path=./ --php_out=proto opentelemetry/proto/trace/v1/trace.proto