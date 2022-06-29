## Development
For repeatability and consistency across different operating systems, we use the [3 Musketeers pattern](https://3musketeers.io/). If you're on Windows, it might be a good idea to use Git bash for following the steps below.

**Note: After cloning the repository, copy `.env.dist` to `.env`.** 

Skipping the step above would result in a "`The "PHP_USER" variable is not set. Defaulting to a blank string`" warning

We use `docker` and `docker-compose` to perform a lot of our static analysis and testing. If you're planning to develop for this library, it'll help to install `docker engine` and `docker-compose`.

The installation instructions for these tools are [here](https://docs.docker.com/install/), under the `Docker Engine` and `Docker Compose` submenus respectively.

To ensure you have all the correct packages installed locally in your dev environment, you can run

```bash
make install
```

This will install all the library dependencies to
the `/vendor` directory.

To update these dependencies, you can run

```bash
make update
```
