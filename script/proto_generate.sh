GIT_IS_AVAILABLE=$(git --version 2>&1 >/dev/null)
PROTOC_IS_AVAILABLE=$(protoc --version 2>&1 >/dev/null)
if  $GIT_IS_AVAILABLE && $PROTOC_IS_AVAILABLE 
then
	cd $(git rev-parse --show-toplevel)
	if [ -d "./opentelemetry-proto" ]; then
		echo "updating opentelemetry-proto"
		cd opentelemetry-proto
		git pull
		cd ..
	else
		git clone https://github.com/open-telemetry/opentelemetry-proto
	fi
	if [ -d "./grpc" ]; then
		rm -rf grpc
	fi
	git clone -b v1.33.x https://github.com/grpc/grpc
	cd grpc
	git submodule update --init
	make grpc_php_plugin
	cd ..
	if [ -d "./proto" ]; then
		echo "removing proto folder..."
		rm -r proto
	fi
	mkdir proto
	protoc --proto_path=opentelemetry-proto/ --php_out=proto --grpc_out=proto --plugin=protoc-gen-grpc=grpc/bins/opt/grpc_php_plugin $(find opentelemetry-proto/opentelemetry -iname "*.proto")
	echo "proto folder created."
else
	echo "git or protoc not available"
fi