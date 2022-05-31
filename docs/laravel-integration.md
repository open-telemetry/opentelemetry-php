# Exploring Opentelemetry in Laravel Applications

## Introduction

Distributed tracing helps developers and management gain insights into how well applications perform in terms of traces,
metrics, and logs. This guide shows how developers can integrate OpenTelemetry PHP into their Laravel applications for
the above benefits. Our example application visualizes exceptions from a Laravel application using both Jaeger and
Zipkin.

To follow this guide you will need:

* PHP Installed; this example uses PHP 7.4.
* [Composer](https://getcomposer.org/download/ ) for dependency management.
* [Docker](https://docs.docker.com/get-docker/) for bundling our visualization tools. We have setup instructions for
  docker on this project's [readme](https://github.com/open-telemetry/opentelemetry-php#development).

This example uses Laravel version 8.40.

> ⚠ This example is only intended to introduce how OpenTelemetry can be used in a Laravel application. The
> example code is not suited for production applications, and must not be consulted for any code that goes into
> production.

## Step 1 - Creating a Laravel Application

The Laravel framework supports creating applications using composer. To do that,
run `composer create-project <project-name>`. We are naming our project `otel-php-laravel-basic-example`, so the
command is as follows:

`composer create-project laravel/laravel otel-php-laravel-basic-example`

To confirm that our application works, we can move to the application directory
using `cd otel-php-laravel-basic-example` , then serve the application with `php artisan serve` .

![image](https://user-images.githubusercontent.com/22311928/115635306-5585e600-a303-11eb-8943-f50846b293b3.png)

Let's navigate to `http://127.0.0.1:8000` on our browser to see the default Laravel welcome page.

![image](https://user-images.githubusercontent.com/22311928/115635309-56b71300-a303-11eb-97bc-7c64e3f4da97.png)

## Step 2 - Require OpenTelemetry PHP Package

Starting from version `v.0.0.2`, the open-telemetry php package allows users to use their preferred HTTP layers for
exporting traces. The benefit of this is that users can reuse already existing HTTP configurations for their
applications. Hence, there is need to require packages that satisfy both `psr/http-client-implementation`
and `psr/http-factory-implementation` before requiring the opentelemetry-php package.

By default, the Laravel framework utilizes `guzzlehttp/guzzle` and this satisfies `psr/http-client-implementation`, so
we need to require the `guzzlehttp/psr7` to meet the `psr/http-factory-implementation` requirement. Let's
run `composer require guzzlehttp/psr7:2.0.0-rc1`.

Note: We are specifying `2.0.0-rc1` as that is the release for `guzzlehttp/psr7` that includes HTTP factories as at the
time of writing this guide.

Next, let's run `composer require open-telemetry/opentelemetry` to pull in the openTelemetry-php package.

## Step 3 - Bundle Zipkin and  Jaeger into the Application

To visualize traces exported from our application, we need to integrate open source tracing
tools [Zipkin](https://zipkin.io/) and [Jaeger](https://www.jaegertracing.io/) into our setup using docker.

First, we create a `docker-compose.yaml` file in the root of our project, with content as follows:

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
            COLLECTOR_ZIPKIN_HOST_PORT: 9412

        ports:
            - "9412:9412"
            - "16686:16686"
```

Next, we pull in Zipkin and Jaeger by running `docker-compose up -d`. This might take some time, depending on your
internet connection speed.

![image](https://user-images.githubusercontent.com/22311928/115635312-5880d680-a303-11eb-9f55-bcd80115abc9.png)

We can confirm that Zipkin is up by navigating to `http://localhost:9411/` on our browser. For Jaeger, navigating
to `http://localhost:16686/` on our browser should display the Jaeger home page.

![image](https://user-images.githubusercontent.com/22311928/115635313-59196d00-a303-11eb-817c-37f4f6416fe1.png)

![image](https://user-images.githubusercontent.com/22311928/115635316-59b20380-a303-11eb-86e4-e15d0efd04d7.png)

## Step 5 - Instrument Laravel Application

For this step, we will utilize our OpenTelemetry PHP Library to export traces to both Zipkin and Jaeger.

The default entry point for Laravel applications is the `index.php` file located in the `public` folder. If we navigate
to `public\index.php` we can see that the index file autoloads classes from packages within our vendor folder, making
them easily usable within our application.

```php
require __DIR__.'/../vendor/autoload.php';
```

The other parts of the `index.php` file enable request and response resolution using the application kernel.

```php
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = tap($kernel->handle(
    $request = Request::capture()
))->send();

$kernel->terminate($request, $response);
```

It is worthy of note that resources(namespaces, classes, variables) created within the `index.php` file are available
within the entire application.

To use open-telemetry specific classes within our application we have to import them at the top of our index file, using
the `use` keyword. This is what our list of open-telemetry imported classes should look like:

 ```php
use OpenTelemetry\API\Trace\AbstractSpan;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use Illuminate\Http\Request;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
```

Remember that these imports should go side by side with the default class imports that come with the `index.php` file.

Next, we create a sample recording trace using
the [AlwaysOnSampler](https://github.com/open-telemetry/opentelemetry-php/blob/main/sdk/Trace/Sampler/AlwaysOnSampler.php)
class, just before the app instance is created like below:

Since we are looking to export traces to both Zipkin and Jaeger, we configure a tracer with exporters for both the
services;

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
))->getTracer('Hello World Laravel Web Server');
```

Next we create a span from our tracer. We don't need to bother with sampling here, since it is handled by the sampler
configured as an internal component of the tracer above.

```php
$request = Request::capture();
$span = $tracer->spanBuilder($request->url())->startSpan();
$spanScope = $span->activate();
```

Finally, we end the active spans once and detach the span scope by adding the following block at the end of
the `index.php` file;

```php
$span->end();
$spanScope->detach();
```

Let's confirm that we can see exported traces on both Zipkin and Jaeger. To do that, we need to
reload `http://127.0.0.1:8000` on our browser;

We also need reload both Zipkin and Jaeger on our browser, using the URLs `http://localhost:9411/`
and `http://localhost:16686/`. Do ensure that both your Laravel server and docker instance are running for this step.

For Jaeger under service, you should see a `Hello World Web Server Jaeger` service. Go ahead and click find traces to
see exported traces.

![image](https://user-images.githubusercontent.com/22311928/115635317-5a4a9a00-a303-11eb-9840-2dba0e6475d8.png)

Once we click on `Find Traces`, you should be able to see traces like below:

![image](https://user-images.githubusercontent.com/22311928/115635318-5a4a9a00-a303-11eb-9d4e-69e45a3b810c.png)

We can click on a trace to get more information about the trace.

![image](https://user-images.githubusercontent.com/22311928/115635320-5ae33080-a303-11eb-8956-97d9f1ce0073.png)

For Zipkin, we can visualize our trace by clicking on `Run Query`

![image](https://user-images.githubusercontent.com/22311928/115635321-5b7bc700-a303-11eb-9d9d-19168e50445e.png)

Since resources in Laravel's `public\index.php` file are available to the entire application, we can use any of the
already instantiated tracers to further instrument controllers or any other parts of our application.

Let's create a `Hello` controller to check this out. Run the command `php artisan make:controller HelloController`

![image](https://user-images.githubusercontent.com/22311928/115635322-5c145d80-a303-11eb-8071-1453edbaca14.png)

Next we need to add a route for accessing the controller. To do this we need to utilize the `HelloController` class
within our web routes file located in the `routes\web.php` as follows:

```php
use App\Http\Controllers\HelloController;
```

Next we need to add a route and method for the controller.

```php
Route::get('/hello', [HelloController::class, 'index']);
```  

The above snippet routes every GET request from the `/hello` route on the browser to an index method within
the `HelloController` class. For now, this method does not exist, so we have to add it to our controller as follows

```php
public function index(){
    return "hello";
}
```

Let's confirm that everything works well by visiting the `/hello` route on our browser.

![](https://user-images.githubusercontent.com/22311928/115635323-5c145d80-a303-11eb-869f-6fe18f7f01a4.png)

Now that we have the `index` method working, we can simulate adding an exception event to our Zipkin trace as follows:

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

We need to reload our `http://127.0.0.1:8000/hello` route, then navigate to Zipkin like before, to see that our span
name gets updated to `new name` and our `Exception Example` is visible.

![image](https://user-images.githubusercontent.com/22311928/115635324-5cacf400-a303-11eb-947d-cf8205c0e93b.png)

## Summary

With the above example we have been able to instrument a Laravel application using the OpenTelemetry PHP library. You
can fork the example project [here](https://github.com/prondubuisi/otel-php-laravel-basic-example).
