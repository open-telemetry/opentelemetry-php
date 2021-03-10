# Integrating Opentelemetry PHP into Symfony Applications

## Introduction 

As a developer, you might be wondering how  Telemetry could be beneficial to you. You are not alone.  Without practical examples, the usefulness of distributed tracing can be difficult to grasp for persons without a cloud or site reliability engineering background. This user guide shows how Telemetry could be useful to gain insights into exceptions happening within an application. This example uses the OpenTelemtry PHP library integrated into a Symfony application, bundled with Jaeger and Zipkin, for visualizing data.  
## Prerequisites
To follow this guide you will need:

* PHP Installed, this example uses PHP 7.4 .
* [Composer](https://getcomposer.org/download/ ) for dependency management. 
* [Symfony CLI](https://symfony.com/download) for managing your Symfony application
*  [Docker](https://docs.docker.com/get-docker/) for bundling our visualization tools

## Step 1 - Creating a Symfony Application 

Create a Symfony application by running the command `symfony new my_project_name`. Remember that you need the  [Symfony CLI](https://symfony.com/download) installed for the `symfony` command to work. I am calling this example `otel-php-symfony-basic-example`, so the command is as follows; `symfony new otel-php-symfony-basic-example` .

## Step 2 - Require and Test Symfony Dependencies

We will be adding dependencies to make our development easy. First is the [Symfony maker bundle](https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html) to help us create boilerplate code easily. We can require this by running `composer require symfony/maker-bundle --dev`.

 To define our routes within our controller methods, let's require the Doctrine annotation library by running the command `composer require doctrine/annotations`.

We can test that routes defined within Controllers work by creating a `HelloController.php` file within the `src\Controller` folder as follows:
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
        return new Response('Hello World');
    }
}
```

To check out the routes available in our current project run `php bin/console debug:router`. 

Let's confirm that our application works by running the command `symfony server:start`.

You can navigate to `localhost/hello` route to see the `Hello world` response returned in the HelloController index method above.
![image](https://user-images.githubusercontent.com/22311928/110263970-7810a980-7fb8-11eb-8683-b5f2d8a82c4a.png)


## Step 3 - Require the OpenTelemetry PHP Library

For this step, we require the OpenTelemetry PHP Library by running the command `composer require open-telemetry/opentelemetry:dev-main`. It is worthy of note that this command pulls in the latest changes from the Opentelemetry PHP repository.

## Step 4 - Bundle Zipkin and  Jaeger into the Application

To visualize traces from our application, we have to bundle open source tracing tools [Zipkin](https://zipkin.io/) and [Jaeger](https://www.jaegertracing.io/) into our application using docker. 

Let's add a `docker-compose.yaml` file in the root of our project with the content as follows:

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

To confirm that docker is installed and running on our system, we can run the hello world docker example using the command `docker run -it --rm hello-world`. If everything works well, run  `docker-compose up -d` to pull in Zipkin and Jaeger. This might take some time, depending on your internet connection speed.

We can confirm that Zipkin is up by navigating to `http://localhost:9411/` on our browser. For Jaeger, navigating to `http://localhost:16686/` on our browser should display the Jaeger home page.

![image](https://user-images.githubusercontent.com/22311928/110503699-bfa04e00-80fc-11eb-9186-c9b295d100f4.png)

![image](https://user-images.githubusercontent.com/22311928/110504108-1f96f480-80fd-11eb-9a2b-a7b4faf8b11c.png)


Now it is time to utilize our OpenTelemetry PHP Library to export traces to both Zipkin and Jaeger.

## Step 5

