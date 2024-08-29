# Debugging

The farmOS development Docker image comes pre-installed with
[XDebug](https://xdebug.org) 3, which allows debugger connections on port 9003.

XDebug can be configured to discover the client host automatically with the
following `extra_hosts` and `environment` configuration in `docker-compose.yml`:

    extra_hosts:
    - host.docker.internal:host-gateway
    environment:
      XDEBUG_MODE: debug
      XDEBUG_CONFIG: client_host=host.docker.internal

## PHPStorm

If you are using the PHPStorm IDE, some additional environment variables are
necessary:

    XDEBUG_SESSION: PHPSTORM
    PHP_IDE_CONFIG: serverName=localhost

For example:

    extra_hosts:
    - host.docker.internal:host-gateway
    environment:
      XDEBUG_MODE: debug
      XDEBUG_CONFIG: client_host=host.docker.internal
      XDEBUG_SESSION: PHPSTORM
      PHP_IDE_CONFIG: serverName=localhost

With this configuration in place, enable the "Start listening for PHP Debug
Connections" option. Add a breakpoint in your code, load the page in your
browser, and you should see a prompt appear in PHPStorm that will begin the
debugging session and pause execution at your breakpoint.

This also works with command-line scripts like `drush`. You may need to map the
path to Drush (`vendor/drush`) in the PHPStorm debugger config.
