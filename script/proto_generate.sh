git --version 2>&1 >/dev/null
GIT_IS_AVAILABLE=$?
if [ $GIT_IS_AVAILABLE -eq 0 ]
then
	cd ..
	git clone https://github.com/open-telemetry/opentelemetry-proto
	echo "Creating proto folder ..."
	mkdir proto
	protoc --version 3>&1 >/dev/null
	PROTO_IS_AVAILABLE=$?
	if [ $PROTO_IS_AVAILABLE -eq 0 ]
	then
		protoc --proto_path=opentelemetry-proto/ --php_out=proto $(find opentelemetry-proto/opentelemetry -iname "*.proto")
	else
		echo "protoc not available"
	fi
else
	echo "git not available"
fi
