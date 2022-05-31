# Exploring Opentelemetry in Symfony Applications

## Introduction

As a developer, you might be wondering how OpenTelemetry could be beneficial to you. Without practical examples, the
usefulness of distributed tracing can be difficult to grasp for persons without a cloud or site reliability engineering
background. This user guide shows how OpenTelemetry could be useful to gain insights into exceptions happening within an
application. This example uses the OpenTelemetry PHP library integrated into a Symfony application, bundled with Jaeger
and Zipkin, for visualizing data.

> ⚠ This example is only intended to introduce how OpenTelemetry can be used in a Symfony application. The
> example code is not suited for production applications, and must not be consulted for any code that goes into
> production.

## Prerequisites

To follow this guide you will need:

* PHP Installed, this example uses PHP 7.4.
* [Composer](https://getcomposer.org/download/) for dependency management.
* [Symfony CLI](https://symfony.com/download) for managing your Symfony application.
* [Docker](https://docs.docker.com/get-docker/) for bundling our visualization tools. We have set up instructions for
  docker on this project's [readme](https://github.com/open-telemetry/opentelemetry-php#development).

This example uses Symfony version 5.2 .

## Step 1 - Creating a Symfony Application

Create a Symfony application by running the command `symfony new my_project_name`. We are calling this
example `otel-php-symfony-basic-example`, so the command is as follows;

`symfony new otel-php-symfony-basic-example` .

## Step 2 - Require and Test Symfony Dependencies

To define our routes within our controller methods, let's require the Doctrine annotation library by running the
command `composer require doctrine/annotations`.

We can test that routes defined within Controllers work by creating a `HelloController.php` file within
the `src\Controller` folder as follows:

```php
<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    /**
     * @Route("/hello", name="hello")
     */
    public function index(): Response
    { 
        return new Response('Hello World');
    }
}
```

To check out the routes available in our current project run `php bin/console debug:router`.

![image](https://user-images.githubusercontent.com/22311928/115637047-3e48f780-a307-11eb-9f75-2a5d033b572a.png)

Let's confirm that our application works by running the command `symfony server:start`.

![image](https://user-images.githubusercontent.com/22311928/115637051-4012bb00-a307-11eb-93b4-703b766f6324.png)

You can navigate to `http://127.0.0.1:8000/hello` route to see the `Hello world` response returned from the
HelloController index method above.

![image](https://user-images.githubusercontent.com/22311928/115637055-4143e800-a307-11eb-8bb3-44060e5661fc.png)

## Step 3 - Require the OpenTelemetry PHP Library

Starting from version `v.0.0.2`, the open-telemetry php package allows users to use their preferred HTTP layers for
exporting traces. The benefit of this is that users can reuse already existing HTTP configurations for their
applications. Hence, there is need to require packages that satisfy both `psr/http-client-implementation`
and `psr/http-factory-implementation` before requiring the opentelemetry-php package.

To satisfy these requirements, we'll use the `guzzlehttp/guzzle` to satisfy the `psr/http-client-implementation` and
`guzzlehttp/psr7` to satisfy the `psr/http-factory-implementation`;

```bash
composer require guzzlehttp/guzzle
composer require guzzlehttp/psr7
```

Finally, we require the OpenTelemetry PHP library;

```bash
composer require opentelemetry/opentelemetry-php
```

It is worthy of note that this command pulls in the last stable release for the library.

## Step 4 - Bundle Zipkin and Jaeger into the Application

To visualize traces from our application, we have to bundle open source tracing tools [Zipkin](https://zipkin.io/)
and [Jaeger](https://www.jaegertracing.io/) into our application using docker.

Let's add a `docker-compose.yaml` file in the root of our project with the content as follows:

```yaml
version: '3.7'
services:
    zipkin:
        image: openzipkin/zipkin-slim
        ports:
            - "9411:9411"
    jaeger:
        image: jaegertracing/all-in-one
        environment:
            COLLECTOR_ZIPKIN_HOST_PORT: 9412 # Before version 1.22.0 use COLLECTOR_ZIPKIN_HTTP_PORT
        ports:
            - "9412:9412"
            - "16686:16686"
```

To confirm that docker is installed and running on our system, we can run the hello world docker example using the
command `docker run -it --rm hello-world`. If everything works well, run  `docker-compose up -d` to pull in Zipkin and
Jaeger. This might take some time, depending on your internet connection speed.

![image](https://user-images.githubusercontent.com/22311928/115637058-41dc7e80-a307-11eb-9315-d188d2b84682.png)

We can confirm that Zipkin is up by navigating to `http://localhost:9411/` on our browser. For Jaeger, navigating
to `http://localhost:16686/` on our browser should display the Jaeger home page.

![image](https://user-images.githubusercontent.com/22311928/115637059-42751500-a307-11eb-9b99-d5bbfec54df2.png)

![image](https://user-images.githubusercontent.com/22311928/115637065-430dab80-a307-11eb-8d7b-90d425b32454.png)

Now it is time to utilize our OpenTelemetry PHP Library to export traces to both Zipkin and Jaeger.

## Step 5 - Instrument Symfony Application

The entry point for all Symfony applications is the `index.php` file located in the `public` folder. Let's navigate
to `public\index.php` to see what is happening. It is worthy of note that resources(namespaces, classes, variables)
created within the `index.php` file are available within the entire application, by default the index file imports all
autoloaded classes within the vendor folder. It also imports contents of the `.env` file. The other parts of
the `index.php` file enable debugging as well as support request and response resolution using the application kernel.

To use open-telemetry specific classes we have to import them at the top of our index file, using the `use` keyword.
This is what our imports look like:

```php
use App\Kernel;
use OpenTelemetry\API\Trace\AbstractSpan;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
```

Remember that these imports should go side by side with the default class imports that come with the `index.php` file.

Since we are looking to export traces to both Zipkin and Jaeger, we configure a tracer with trace exporters for both the
services. The tracer needs a sampler to decide whether to record a trace or not. We use the `AlwaysOnSampler` here to
ensure that all traces are recorded.

```php
$httpClient = new Client();
$httpFactory = new HttpFactory();

$tracer = (new TracerProvider(
    [
        new SimpleSpanProcessor(
            new OpenTelemetry\Contrib\Jaeger\Exporter(
                'Hello World Web Server Jaeger',
                'http://localhost:9412/api/v2/spans',
                $httpClient,
                $httpFactory,
                $httpFactory,
            ),
        ),
        new SimpleSpanProcessor(
            new OpenTelemetry\Contrib\Zipkin\Exporter(
                'Hello World Web Server Zipkin',
                'http://localhost:9411/api/v2/spans',
                $httpClient,
                $httpFactory,
                $httpFactory,
            ),
        ),
    ],
    new AlwaysOnSampler(),
))->getTracer('Hello World Symfony Web Server');
```

Next we create a span from our tracer. We don't need to bother with sampling here, since it is handled by the sampler
configured as an internal component of the tracer above.

```php
$request = Request::createFromGlobals();
$span = $tracer->spanBuilder($request->getUri())->startSpan();
$spanScope = $span->activate();
```

Finally, we end the active spans and detach the span scope by adding the following block at the end of
the `index.php` file;

```php
$span->end();
$spanScope->detach();
```

Let's confirm that we can see exported traces on both Zipkin and Jaeger. To do that we need to
reload `http://127.0.0.1:8000/hello` or any other route on our symfony server;

![image](https://user-images.githubusercontent.com/22311928/115637070-43a64200-a307-11eb-8abb-80f19285ef01.png)

We also need reload both Zipkin and Jaeger on our browser, using the URLs `http://localhost:9411/`
and `http://localhost:16686/`. Do ensure that both your symfony server and docker instance are running for this step.

For Jaeger under service, you should see a `Hello World Web Server Jaeger` service, go ahead and click find traces to
see exported traces.

)
![image](https://user-images.githubusercontent.com/22311928/115637071-443ed880-a307-11eb-8521-78c803dcd623.png)

Once we click on `Find Traces` you should be able to see traces like below:

![image](https://user-images.githubusercontent.com/22311928/115637073-44d76f00-a307-11eb-9e80-aa8f152577a8.png)

We can click on a trace to get more information about the trace.

![image](https://user-images.githubusercontent.com/22311928/115637075-44d76f00-a307-11eb-8d8e-5bf559bd2bf6.png)

For Zipkin, we can visualize our trace by clicking on `Run Query`

![image](https://user-images.githubusercontent.com/22311928/115637078-45700580-a307-11eb-9867-aa0c1a69f9fe.png)

Since resources in Symfony's `public\index.php` file are available to the entire application, we can use any of the
already instantiated tracers within `HelloController`. In addition to the tracers, we can also utilize associated
properties, methods and events.

Let's try using the `addEvent` method, to capture errors within our controller as follows:

```php
/** @var TracerInterface $tracer */
global $tracer;
if ($tracer) {
    /** @var Span $span */
    $span = AbstractSpan::getCurrent();

    $span->setAttribute('foo', 'bar');
    $span->updateName('New name');

    $childSpan = $tracer->spanBuilder('Child span')->startSpan();
    $childScope = $childSpan->activate();
    try {
        throw new \Exception('Exception Example');
    } catch (\Exception $exception) {
        $childSpan->setStatus(StatusCode::STATUS_ERROR, $exception->getMessage());
    }
    $childSpan->end();
    $childScope->detach();
}
```

In the above snippet we change the span name and attributes for our Zipkin trace, we also add an exception event to the
span.

We need to reload our `http://127.0.0.1:8000/hello` route, then navigate to Zipkin like before to see that our span name
gets updated to `new name` and our `Exception Example` is visible

![image](https://user-images.githubusercontent.com/22311928/115637079-46089c00-a307-11eb-9d9d-b5c0f941baeb.png)

## Summary

With the above example we have been able to instrument a Symfony application using the OpenTelemetry php library. You
can fork the example project [here](https://github.com/prondubuisi/otel-php-symfony-basic-example). You can also check
out the original test application [here](https://github.com/dkarlovi/opentelemetry-php-user-test).
