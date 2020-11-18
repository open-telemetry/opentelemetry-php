apk update && apk add git

mkdir /mnt/proto
git clone https://github.com/open-telemetry/opentelemetry-proto

protoc --proto_path=opentelemetry-proto/ --php_out=/mnt/proto --grpc_out=/mnt/proto --plugin=protoc-gen-grpc=usr/local/bin/grpc_php_plugin $(find opentelemetry-proto/opentelemetry -iname "*.proto")
