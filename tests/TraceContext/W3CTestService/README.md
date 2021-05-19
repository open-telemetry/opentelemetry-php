# W3C Trace Context test service

This test service works with the [W3C distributed tracing validation service](https://github.com/w3c/trace-context/tree/master/test). It confirms that the the OpenTelemetry php library meets the requirements for trace context propagation according to the [Trace Context spec](https://www.w3.org/TR/trace-context/).

## Test Run

1.) Ensure the necessary dependencies are installed by running `make install`.  This will install the composer dependencies and store them in `/vendor`.

2.) Execute the test by using `make w3c-test-service`.

## Test service architecture

This test service acts as an endpoint that responds to requests from the [W3C distributed tracing validation service](https://github.com/w3c/trace-context/tree/master/test). The validation service uses HTTP POST to communicate with the test service endpoint, giving instructions via the POST body, and waiting for the service to callback to the harness.

### HTTP POST body format

The HTTP POST request body from the validation service is a JSON array and each element in the array is an object with two properties `url` and `arguments`. The test service iterates through the JSON array, and for each element, sends a HTTP POST to the specified `url`, with `arguments` as the request body.

Below is a sample request from the test harness:

```
POST /test HTTP/1.1
Accept: application/json
Accept-Encoding: gzip, deflate
Host: 127.0.0.1:5000
User-Agent: Python/3.7 aiohttp/3.3.2
Content-Length: 118
Content-Type: application/json

[
    {"url": url1, "arguments": [
        {"url": url2, "arguments": []}
    ]},
    {"url": url3, "arguments": []}
]
```

The validation service then checks the `traceparent` and `tracestate` headers in the response from the test service. For more information on the test cases, please reference the [validation service](https://github.com/w3c/trace-context/tree/master/test#run-test-cases) docs. 

### Test service

The test service is created using a `symfony` application that has a `/test` endpoint. Once this endpoint is hit by the validation service, the test service creates a `Baggage` using the received `traceparent` and `tracestate` headers. These headers are used to propagate the trace.

