# Containerized project template for Drupal 8.9 & 9.x projects with Composer, Phing and Behat
The DP-Template project is an opinionated local development environment built around
the concept of simplicity, transparency, expandability, and an _exceptionally_ low
barrier to entry. Built on docker, the DP-Template project should function identically
on all* host systems capable of running docker images.

Cloning this repository will give you a stock Drupal development site running in 
docker containers. GNU Make commands assist in setup, management, and cleanup. Additional
commands can be easily added into the system to satisfy independent project needs.

Files are synced locally allowing you to do local development work using the tools and
functionality you and your developers are most comfortable with. A single codebase houses
all of the code needed to replicate an entire Drupal environment identically on multiple
machines with no configuration needed.

While focused on development tasks, the project also offers some production deployment
tools to ease the transition of a local codebase into a production ready deployment.

This project template should provide a kickstart for managing your site
dependencies with [Composer](https://getcomposer.org/) and configurations and tasks with [PHing](https://www.phing.info/).

If you want to know how to use it as replacement for
[Drush Make](https://github.com/drush-ops/drush/blob/master/docs/make.md) visit
the [Documentation on drupal.org](https://www.drupal.org/node/2471553).

You'll get also [behat](http://behat.org/), [PHPUnit](https://phpunit.de/), 
[PHP_CodeSniffer](https://www.squizlabs.com/php-codesniffer) with the Drupal project 
[Coder](https://www.drupal.org/project/coder) as Quality Assurance tools for your development environments.

# Install
The instructions assume that you have already installed [Docker](https://docs.docker.com/installation/) and [Docker Compose](https://docs.docker.com/compose/install/). 

In order to get started be sure to clone this project onto your Docker Host. Create a directory on your host. Please note that the webservices will inherit the name from the directory you create. If you create a folder named test. Then the services will all be named test_web_1, test_db_1, test_pma_1.

    
    git clone https://github.com/estoclet/dp-template.git .
    

# How to get up and running
## Step 1 : launch the containers
Once you've cloned the project to your host we can now start it. Easy! Navigate to the directory in which you cloned the project. Run the following commands from this directory 
    

    docker-compose up -d

The  docker-compose command will pull the images from Docker Hub and then link them together based on the information inside the docker-compose.yml file. This will create ports, links between containers, and configure applications as required. After the command completes we can now view the status of our stack

    docker-compose ps

You should see something like this:

        Name                  Command               State           Ports
    -----------------------------------------------------------------------------
    dp-template_db_1    docker-entrypoint.sh /usr/ ...   Up      0.0.0.0:3306->3306/tcp
    dp-template_pma_1   /docker-entrypoint.sh apac ...   Up      0.0.0.0:8001->80/tcp
    dp-template_web_1   docker-entrypoint.sh apach ...   Up      0.0.0.0:80->80/tcp

## Step 2 : Download Drupal in your container
Next, execute an interactive bash shell on the container.

    $ docker exec -it dp-template_web_1 bash

This will create a new Bash session in the container dp-template_web_1.
Now, move to the project root folder :

    # cd /var/www/

And, finally, install drupal with composer:

    # composer install

You should now access to your Drupal locale instance at  [http://localhost](http://localhost/).

## Configuration

Rename `phing/example.build.properties.local` to `phing/build.properties.local` under [root]/phing.

This file will contain configuration which is unique to your local/development
machine. This is mainly useful for specifying your database credentials and the
username and password of the Drupal admin user so they can be used during the
installation.

Because these settings are personal they should not be shared with the rest of
the team. Make sure you never commit this file!

All options related to your project can be found in the `phing/build.properties.project` file. 

Global options (in most cases you don't need to modify this file) can be found in the 
`phing/build.properties.global` file.

Just copy the lines you want to override and change their values.

Please take knowledge of the following override-flow of settings:

`phing/build.properties.local` overrides 

`phing/build.properties.project` which overrides 

`phing/build.properties.global`.

## What does the template do?

When installing the given `composer.json` some tasks are taken care of:

* Drupal will be installed in the `htdocs`-directory.
* Autoloader is implemented to use the generated composer autoloader in `vendor/autoload.php`,
  instead of the one provided by Drupal (`web/vendor/autoload.php`).
* Modules (packages of type `drupal-module`) will be placed in `web/modules/contrib/`
* Theme (packages of type `drupal-theme`) will be placed in `web/themes/contrib/`
* Profiles (packages of type `drupal-profile`) will be placed in `web/profiles/contrib/`
* Creates default writable versions of `settings.php` and `services.yml`.
* Creates `sites/default/files`-directory.
* Latest version of drush is installed locally for use at `vendor/bin/drush`.
* Latest version of DrupalConsole is installed locally for use at `vendor/bin/drupal`.

## Updating Drupal Core

This project will attempt to keep all of your Drupal Core files up-to-date; the 
project [drupal-composer/drupal-scaffold](https://github.com/drupal-composer/drupal-scaffold) 
is used to ensure that your scaffold files are updated every time drupal/core is 
updated. If you customize any of the "scaffolding" files (commonly .htaccess), 
you may need to merge conflicts if any of your modfied files are updated in a 
new release of Drupal core.

Follow the steps below to update your core files.

1. Run `composer update drupal/core --with-dependencies` to update Drupal Core and its dependencies.
1. Run `git diff` to determine if any of the scaffolding files have changed. 
   Review the files for any changes and restore any customizations to 
  `.htaccess` or `robots.txt`.
1. Commit everything all together in a single commit, so `web` will remain in
   sync with the `core` when checking out branches or running `git bisect`.
1. In the event that there are non-trivial conflicts in step 2, you may wish 
   to perform these steps on a branch, and use `git merge` to combine the 
   updated core files with your customized files. This facilitates the use 
   of a [three-way merge tool such as kdiff3](http://www.gitshah.com/2010/12/how-to-setup-kdiff-as-diff-tool-for-git.html). 
   This setup is not necessary if your changes are simple; 
   keeping all of your modifications at the beginning or end of the file is a 
   good strategy to keep merges easy.


## PHING: Listing the available build commands

You can get a list of all the available Phing build commands ("targets") with a
short description of each target with the following command:

```
$ ./vendor/bin/phing
```


## Install the website.

```
$ ./vendor/bin/phing install
```


## Set up tools for the development environment

You can set up a development environment quickly by executing `install-dev`:

```
$ ./vendor/bin/phing install-dev
```
This command perform both the `install` and `setup-dev` targets at once and you should be ready to code. 

If you want to go further on preparing your local installation for front-development:
Please modify the `/sites/developement.services.yml` with the following configuration:

```
parameters:
  twig.config:
    debug: true
    auto_reload: true
    cache: false
services:
  cache.backend.null:
    class: Drupal\Core\Cache\NullBackendFactory
```

If you want to know more about the `setup-dev` Phing target:

```
$ ./vendor/bin/phing setup-dev
```

This will perform the following tasks:

1. Configure Behat.
2. Configure PHP CodeSniffer.
3. Enable 'development mode'. This will:
  * Enable the services in `development.services.yml`.
  * Show all error messages with backtrace information.
  * Disable CSS and JS aggregation.
  * Disable the render cache.
  * Allow test modules and themes to be installed.
  * Enable access to `rebuild.php`.
4. Enable development modules.
5. Create a demo user for each user role.


## Running Behat tests

The Behat test suite is located in the `tests/` folder. The easiest way to run
them is by going into this folder and executing the following command:

```
$ cd tests/
$ ./behat
```

If you want to execute a single test, just provide the path to the test as an
argument. The tests are located in `tests/features/`:

```
$ cd tests/
$ ./behat features/authentication.feature
```

If you want to run the tests from a different folder, then provide the path to
`tests/behat.yml` with the `-c` option:

```
# Run the tests from the root folder of the project.
$ ./vendor/bin/behat -c tests/behat.yml
```


## Running PHPUnit tests

Run the tests from the `web` folder:

```
$ cd web/
$ ../vendor/bin/phpunit
```

By default all tests in the folders `web/modules/custom`, `web/profiles` and
`web/themes/custom` are included when running the tests. Check the section on
PHPUnit in the `build.properties.dist` to customize the tests.


## Checking for coding standards violations

### Set up PHP CodeSniffer

PHP CodeSniffer is included to do coding standards checks of PHP and JS files.
In the default configuration it will scan all files in the following folders:
- `web/modules` (excluding `web/modules/contrib`)
- `web/profiles`
- `web/themes`

First you'll need to execute the `setup-php-codesniffer` Phing target (note that
this also runs as part of the `install-dev` and `setup-dev` targets):

```
$ ./vendor/bin/phing setup-php-codesniffer
```

This will generate a `phpcs.xml` file containing settings specific to your local
environment. Make sure to never commit this file.

### Run coding standards checks

#### Run checks manually

The coding standards checks can then be run as follows:

```
# Scan all files for coding standards violations.
$ ./vendor/bin/phpcs

# Scan only a single folder.
$ ./vendor/bin/phpcs web/modules/custom/mymodule
```

#### Run checks automatically when pushing

To save yourself the embarrassment of pushing non-compliant code to the git
repository you can put the following line in your `build.properties.local`:

```
# Whether or not to run a coding standards check before doing a git push. Note
# that this will abort the push if the coding standards check fails.
phpcs.prepush.enable = 1
```

and then regenerate your PHP CodeSniffer configuration:

```
$ ./vendor/bin/phing setup-php-codesniffer
```

If your project requires all team members to follow coding standards, put this
line in the project configuration (`build.properties`) instead.

Note that this will not allow you to push any code that fails the coding
standards check. If you really need to push in a hurry, then you can disable
the coding standards check by executing this Phing target:

```
$ ./vendor/bin/phing disable-pre-push
```

The pre-push hook will be reinstated when the `setup-php-codesniffer` target
is executed.


### Customize configuration

The basic configuration can be changed by copying the relevant Phing properties
from the "PHP CodeSniffer configuration" section in `build.properties.global` to
`build.properties.project` and changing them to your requirements. Then regenerate the
`phpcs.xml` file by running the `setup-php-codesniffer` target:

```
$ ./vendor/bin/phing setup-php-codesniffer
```

To change to PHP CodeSniffer ruleset itself, make a copy of the file
`phpcs-ruleset.xml.dist` and rename it to `phpcs-ruleset.xml`, and then put this
line in your `build.properties` file:

```
phpcs.standard = ${project.basedir}/phpcs-ruleset.xml
```

For more information on configuring the ruleset see [Annotated ruleset](http://pear.php.net/manual/en/package.php.php-codesniffer.annotated-ruleset.php).


## FAQ

### Should I commit the contrib modules I download

Composer recommends **no**. They provide [argumentation against but also 
workrounds if a project decides to do it anyway](https://getcomposer.org/doc/faqs/should-i-commit-the-dependencies-in-my-vendor-directory.md).

### How can I apply patches to downloaded modules?

If you need to apply patches (depending on the project being modified, a pull 
request is often a better solution), you can do so with the 
[composer-patches](https://github.com/cweagans/composer-patches) plugin.

To add a patch to drupal module foobar insert the patches section in the extra 
section of composer.json:
```json
"extra": {
    "patches": {
        "drupal/foobar": {
            "Patch description": "URL to patch"
        }
    }
}
```

## Credits
https://github.com/drupal-composer/drupal-project

https://github.com/pfrenssen/drupal-project
