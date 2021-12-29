apk update && apk add git

DESTINATION_DIR=/mnt/proto/otel
REPO_DIR=opentelemetry-proto

mkdir -p $DESTINATION_DIR
rm -R ./$REPO_DIR
git clone https://github.com/open-telemetry/$REPO_DIR

TAG=$(
    # shellcheck disable=SC2164
    cd ./$REPO_DIR

    # shellcheck disable=SC2046
    # shellcheck disable=SC2006
    TAG=$(git describe --tags `git rev-list --tags --max-count=1`)

    git checkout "${TAG}"

    echo "$TAG"
)

echo "Generating protobuf files for version $TAG ..."

# shellcheck disable=SC2046
protoc --proto_path=$REPO_DIR/ --php_out=$DESTINATION_DIR --grpc_out=$DESTINATION_DIR \
 --plugin=protoc-gen-grpc=usr/local/bin/grpc_php_plugin $(find $REPO_DIR/opentelemetry/proto -iname "*.proto")

echo "$TAG" > $DESTINATION_DIR/VERSION

echo "Done!"
