protoc --version 2>&1 >/dev/null
GIT_IS_AVAILABLE=$?
if [ $GIT_IS_AVAILABLE -eq 0 ]
then
	cd ..
	git clone https://github.com/open-telemetry/opentelemetry-proto
	mkdir proto
	protoc --version 3>&1 >/dev/null
	PROTO_IS_AVAILABLE=$?
	if [ $PROTO_IS_AVAILABLE -eq 0 ]
	then
		protoc --proto_path=opentelemetry-proto/ --php_out=proto opentelemetry-proto/opentelemetry/proto/collector/logs/v1/logs_service.proto
		protoc --proto_path=opentelemetry-proto/ --php_out=proto opentelemetry-proto/opentelemetry/proto/collector/metrics/v1/metrics_service.proto
		protoc --proto_path=opentelemetry-proto/ --php_out=proto opentelemetry-proto/opentelemetry/proto/collector/trace/v1/trace_service.proto
		protoc --proto_path=opentelemetry-proto/ --php_out=proto opentelemetry-proto/opentelemetry/proto/common/v1/common.proto
		protoc --proto_path=opentelemetry-proto/ --php_out=proto opentelemetry-proto/opentelemetry/proto/logs/v1/logs.proto
		protoc --proto_path=opentelemetry-proto/ --php_out=proto opentelemetry-proto/opentelemetry/proto/metrics/experimental/configservice.proto
		protoc --proto_path=opentelemetry-proto/ --php_out=proto opentelemetry-proto/opentelemetry/proto/metrics/v1/metrics.proto
		protoc --proto_path=opentelemetry-proto/ --php_out=proto opentelemetry-proto/opentelemetry/proto/resource/v1/resource.proto
		protoc --proto_path=opentelemetry-proto/ --php_out=proto opentelemetry-proto/opentelemetry/proto/trace/v1/trace_config.proto
		protoc --proto_path=opentelemetry-proto/ --php_out=proto opentelemetry-proto/opentelemetry/proto/trace/v1/trace.proto
	else
		echo "protoc not available"
	fi
else
	echo "git not available"
fi
