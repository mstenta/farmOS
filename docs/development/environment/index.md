# Getting started

Follow these instructions to set up a local farmOS development environment.

The only requirements are [Docker](https://www.docker.com) and
[Docker Compose](https://docs.docker.com/compose).

## 1. Set up Docker containers

Run the following commands to create a farmOS directory and set up Docker
containers for farmOS and PostgreSQL:

    mkdir farmOS && cd farmOS
    curl https://raw.githubusercontent.com/farmOS/farmOS/3.x/docker/docker-compose.development.yml -o docker-compose.yml
    docker compose up -d

## 2. Install farmOS

Open `http://localhost` in a browser and install farmOS with the following
database credentials:

- Database type: **PostgreSQL**
- Database name: `farm`
- Database user: `farm`
- Database password: `farm`
- Advanced options > Host: `db`

## 3. Develop

After starting the Docker containers, the root `farmOS` directory will contain
two new subdirectories: `www` and `db`.

The `www` directory contains the fully built farmOS codebase, which is
bind-mounted into the `www` container's `/opt/drupal` directory. The `www/web`
directory is used as the Apache webroot. Loading the `www` directory in your
favorite PHP IDE will provide easy code access to the full Symfony + Drupal +
farmOS stack.

The `db` directory contains the PostgreSQL database files, which is
bind-mounted into the `db` container's `/var/lib/postgresql/data` directory.
With the containers stopped, this directory can be backed up (eg: via tarball)
to create snapshots for easy rollback during development.

## Optional

### Set up local repositories

1. Stop running containers.
2. Add a `./repos:/opt/repos` volume to the `www` service in
   `docker-compose.yml`.
3. Start containers.
4. Change ownership of `./repos`: `sudo chown ${USER}:${USER} ./repos`
5. Clone your fork of the farmOS Git repository:

        git clone git@github.com:farmOS/farmOS.git repos/farmOS

6. Tell Composer to use the local repository (see [Composer](./composer) for
   how to run `composer` commands in the development environment):

        composer config repositories.farmos path ../repos/farmOS
        composer update
        composer require farmos/farmos:*

Additional repositories can be added similarly (eg: for contrib module
development). For example, this will check out the `farm_bee` module repository:

    git clone git@github.com:farmOS/farm_bee.git repos/farm_bee
    composer config repositories.farm_bee path ../repos/farm_bee
    composer require drupal/farm_bee:*

### Configure private filesystem

In order to upload files, a private file path must be configured. The following
line must be added to `www/web/sites/default/settings.php`:

    $settings['file_private_path'] = '/opt/drupal/web/sites/default/private/files';

Additionally, create the folder `/opt/drupal/web/sites/default/private/`.

Set the correct user and permissions:

Folder ownership and group should match the web server user. If you are using
the farmOS Docker image (running Apache), this will be `www-data`.

Folder permissions should be set to `770` or `drwxrwx---`.

Finally, make sure to clear the caches by visiting Administration >
Configuration > Development > Performance and clicking the `Clear all caches`
button, or use Drush via the command line: `drush cr`.

### Configure debugger

See [Debugging](/development/environment/debug).

### Enable HTTPS

See [HTTPS](/development/environment/https).
