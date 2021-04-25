# Integrating Opentelemetry PHP into Symfony Applications

## Introduction 

As a developer, you might be wondering how OpenTelemetry could be beneficial to you. Without practical examples, the usefulness of distributed tracing can be difficult to grasp for persons without a cloud or site reliability engineering background. This user guide shows how OpenTelemetry could be useful to gain insights into exceptions happening within an application. This example uses the OpenTelemtry PHP library integrated into a Symfony application, bundled with Jaeger and Zipkin, for visualizing data.  
## Prerequisites
To follow this guide you will need:

* PHP Installed, this example uses PHP 7.4.
* [Composer](https://getcomposer.org/download/ ) for dependency management. 
* [Symfony CLI](https://symfony.com/download) for managing your Symfony application.
* [Docker](https://docs.docker.com/get-docker/) for bundling our visualization tools. We have setup instructions for docker on this project's [readme](https://github.com/open-telemetry/opentelemetry-php#development).

This example uses Symfony version 5.2 .

## Step 1 - Creating a Symfony Application 

Create a Symfony application by running the command `symfony new my_project_name`. We are calling this example `otel-php-symfony-basic-example`, so the command is as follows; 

`symfony new otel-php-symfony-basic-example` .

## Step 2 - Require and Test Symfony Dependencies

 To define our routes within our controller methods, let's require the Doctrine annotation library by running the command `composer require doctrine/annotations`.

We can test that routes defined within Controllers work by creating a `HelloController.php` file within the `src\Controller` folder as follows:
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



You can navigate to `http://127.0.0.1:8000/hello` route to see the `Hello world` response returned from the HelloController index method above.

![image](https://user-images.githubusercontent.com/22311928/115637055-4143e800-a307-11eb-8bb3-44060e5661fc.png)



## Step 3 - Require the OpenTelemetry PHP Library

For this step, we require the OpenTelemetry PHP Library by running the command `composer require open-telemetry/opentelemetry`. It is worthy of note that this command pulls in the last stable release for the library.

## Step 4 - Bundle Zipkin and  Jaeger into the Application

To visualize traces from our application, we have to bundle open source tracing tools [Zipkin](https://zipkin.io/) and [Jaeger](https://www.jaegertracing.io/) into our application using docker. 

Let's add a `docker-compose.yaml` file in the root of our project with the content as follows:

```yaml
version: '3.7'
services:
    zipkin:
        image: openzipkin/zipkin-slim
        ports:
            - 9411:9411
    jaeger:
        image: jaegertracing/all-in-one
        environment:
            COLLECTOR_ZIPKIN_HTTP_PORT: 9412
        ports:
            - 9412:9412
            - 16686:16686
```

To confirm that docker is installed and running on our system, we can run the hello world docker example using the command `docker run -it --rm hello-world`. If everything works well, run  `docker-compose up -d` to pull in Zipkin and Jaeger. This might take some time, depending on your internet connection speed.

![image](https://user-images.githubusercontent.com/22311928/115637058-41dc7e80-a307-11eb-9315-d188d2b84682.png)


We can confirm that Zipkin is up by navigating to `http://localhost:9411/` on our browser. For Jaeger, navigating to `http://localhost:16686/` on our browser should display the Jaeger home page.

![image](https://user-images.githubusercontent.com/22311928/115637059-42751500-a307-11eb-9b99-d5bbfec54df2.png)

![image](https://user-images.githubusercontent.com/22311928/115637065-430dab80-a307-11eb-8d7b-90d425b32454.png)


Now it is time to utilize our OpenTelemetry PHP Library to export traces to both Zipkin and Jaeger.

## Step 5 - Instrument Symfony Application

The entry point for all Symfony applications is the `index.php` file located in the `public` folder. Let's navigate to `public\index.php` to see what is happening. It is worthy of note that resources(namespaces, classes, variables) created within the `index.php` file are available within the entire application, by default the index file imports all auto loaded classes within the vendor folder. It also imports contents of the `.env` file. The other parts of the `index.php` file enable debugging as well as support request and response resolution using the application kernel. 

To use open-telemetry specific classes we have to import them at the top of our index file, using the `use` keyword. This is what our imports look like:

```php
use App\Kernel;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\Sdk\Trace\Clock;
use OpenTelemetry\Sdk\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\Sdk\Trace\SamplingResult;
use OpenTelemetry\Sdk\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\Sdk\Trace\TracerProvider;
use OpenTelemetry\Trace as API;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;
```

Next, we create a sample recording trace using the [AlwaysOnSampler](https://github.com/open-telemetry/opentelemetry-php/blob/main/sdk/Trace/Sampler/AlwaysOnSampler.php) class, just before the Kernel instance is created like below:

```php
$sampler = new AlwaysOnSampler();
$samplingResult = $sampler->shouldSample(
    null,
    md5((string) microtime(true)),
    substr(md5((string) microtime(true)), 16),
    'io.opentelemetry.example',
    API\SpanKind::KIND_INTERNAL
);
```

Since we are looking to export traces to both Zipkin and Jaeger we have to make use of their individual exporters;

```php
$jaegerExporter = new JaegerExporter(
    'Hello World Web Server Jaeger',
    'http://localhost:9412/api/v2/spans'
);

$zipkinExporter = new ZipkinExporter(
    'Hello World Web Server Zipkin',
    'http://localhost:9411/api/v2/spans'
);
```

Next we create a trace, and add processors for each trace(One for Jaeger and another for Zipkin). Then we proceed to start and activate a span for each trace. We create a trace only if the RECORD AND SAMPLED sampling result condition passes as follows;

```php
if (SamplingResult::RECORD_AND_SAMPLED === $samplingResult->getDecision()) {

    $jaegerTracer = (new TracerProvider())
        ->addSpanProcessor(new BatchSpanProcessor($jaegerExporter, Clock::get()))
        ->getTracer('io.opentelemetry.contrib.php');

    $zipkinTracer = (new TracerProvider())
    ->addSpanProcessor(new BatchSpanProcessor($zipkinExporter, Clock::get()))
    ->getTracer('io.opentelemetry.contrib.php');

    $request = Request::createFromGlobals();
    $jaegerSpan = $jaegerTracer->startAndActivateSpan($request->getUri());
    $zipkinSpan = $zipkinTracer->startAndActivateSpan($request->getUri());

}
```

Finally we end the active spans if sampling is complete, by adding the following block at the end of the `index.php` file;

```php
if (SamplingResult::RECORD_AND_SAMPLED === $samplingResult->getDecision()) {
    $zipkinTracer->endActiveSpan();
    $jaegerTracer->endActiveSpan();
}
```

lets confirm that we can see exported traces on both Zipkin and Jaeger. To do that we need to reload `http://127.0.0.1:8000/hello` or any other route on our symfony server;

![image](https://user-images.githubusercontent.com/22311928/115637070-43a64200-a307-11eb-8abb-80f19285ef01.png)

We also need reload both Zipkin and Jaeger on our browser, using the URLs `http://localhost:9411/`  and `http://localhost:16686/`. Do ensure that both your symfony server and docker instance are running for this step. 

For Jaeger under service, you should see a `Hello World Web Server Jaeger` service, go ahead and click find traces to see exported traces.

)
![image](https://user-images.githubusercontent.com/22311928/115637071-443ed880-a307-11eb-8521-78c803dcd623.png)


Once we click on `Find Traces` you should be able to see traces like below:

![image](https://user-images.githubusercontent.com/22311928/115637073-44d76f00-a307-11eb-9e80-aa8f152577a8.png)


We can click on a trace to get more information about the trace. 

![image](https://user-images.githubusercontent.com/22311928/115637075-44d76f00-a307-11eb-8d8e-5bf559bd2bf6.png)


For Zipkin, we can visualize our trace by clicking on `Run Query` 


![image](https://user-images.githubusercontent.com/22311928/115637078-45700580-a307-11eb-9867-aa0c1a69f9fe.png)

Since resources in Symfony's `public\index.php` file are available to the entire application, we can use any of the already instantiated tracers within `HelloController`. In addition to the tracers, we can also utilize associated properties, methods and events.

Lets try using the `addEvent` method, to capture errors within our controller as follows:

```php
global $zipkinTracer;
        if ($zipkinTracer) {
            /** @var Span $span */
            $span = $zipkinTracer->getActiveSpan();
            
            $span->setAttribute('foo', 'bar');
            $span->updateName('New name');

            $zipkinTracer->startAndActivateSpan('Child span');
            try {
                throw new \Exception('Exception Example');
            } catch (\Exception $exception) {
                $span->setSpanStatus($exception->getCode(), $exception->getMessage());
            }
            $zipkinTracer->endActiveSpan();
        }
```
In the above snippet we change the span name and attributes for our Zipkin trace, we also add an exception event to the span.

We need to reload our `http://127.0.0.1:8000/hello` route, then navigate to Zipkin like before to see that our span name gets updated to `new name` and our `Exception Example` is visible 

![image](https://user-images.githubusercontent.com/22311928/115637079-46089c00-a307-11eb-9d9d-b5c0f941baeb.png)

## Summary

With the above example we have been able to instrument a Symfony application using the OpenTelemetry php library. You can fork the example project [here](https://github.com/prondubuisi/otel-php-symfony-basic-example). You can also checkout the original test application [here](https://github.com/dkarlovi/opentelemetry-php-user-test).
