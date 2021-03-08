# Integrating Opentelemetry PHP into Symfony Applications

## Introduction 
As a PHP developer I have always wondered how OpenTelemetry PHP could be beneficial to me. This user guide explains how one can instrument and gain insights from their Symfony application using the OpenTelemetry library. This guide highlights examples on using the OpenTelemtry PHP API. 
## Set Up
Ensure you have the following

* This assumes that you have PHP Installed
* You need [composer](https://getcomposer.org/download/) installed
* You need [Symfony CLI](https://symfony.com/download) installed 
* You need [docker](https://docs.docker.com/get-docker/) Installed

## Step 1
Create a Symfony application by running the command ` composer create-project symfony/skeleton my_project_name` for this example I am calling the project [otel-php-symfony-basic-example](https://github.com/prondubuisi/otel-php-symfony-basic-example)

## Step 2
Add [Symfony maker bundle](https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html) to help us create boiler plate code easily by running `composer require symfony/maker-bundle --dev`. To easily define our routes within our controller methods lets require the Doctrine annotation library by running the command `composer require doctrine/annotations`

Test that routes defined within Controllers work by creating a HelloController.php file within `src\Controller` folder
```
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
        usleep(30000);
        
        return new Response('Hello World');
    }
}
```

Checkout the routes available in your project by running `php bin/console debug:router`. Serve the application by running `symfony server:start` you can navigate to your localhost/hello route to see the Hello world` example defined in the HelloController index method above.
![image](https://user-images.githubusercontent.com/22311928/110263970-7810a980-7fb8-11eb-8683-b5f2d8a82c4a.png)


## Step 3
Require OpenTelemetry PHP by running the command `composer require open-telemetry/opentelemetry`

## Step 4
For this test example it is neccessary that we visualize traces from our application, and to archieve this we have to need to bundle open source tracing tools like [Zipkin](https://zipkin.io/) and [Jaegar](https://www.jaegertracing.io/) into out application using docker. 

To achieve this lets add a `docker-compose.yaml` file to the root of our project with the following content

```
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
Ensure that docker is available and running on your system Next we need to run `docker-compose up -d`

Confirm that Zipkin is up by navigating to `http://localhost:9411/` on your browser

Confirm that Jenger is up by navigating to `http://localhost:16686/` on your browser

Now it is time to utilize our OpenTelemetry PHP Library to export traces to both Zipkin and Jaegar.

## Step 5

