# Bootstrapping ZF1

Apigility can directly connect with your database and offer a full REST API for your application, but in most cases you already have an application build with Zend Framework 1.x (ZF1). Let's assume you have incorporated a lot of business logic in this application so it would be a waste not to use it building a rich REST API.

This article describes what needs to be done to incorporate your ZF1 application into a vanilla installation of Apigility. It will not describe the installation of Apigility as this is fully documented on the [Apigility website](http://www.apigility.org).

**NOTE:** We use our [demo ZF1 application](https://github.com/in2it/zfdemo) as example.

## Including your ZF1 application into Apigility

The easiest way is to use [Gitmodules](http://git-scm.com/docs/gitmodules.html) if you have your ZF1 project in GIT. If you have your ZF1 project in SVN, you need to do a manual checkout of the project.

Since Apigility uses [Composer](http://getcomposer.org) for installation and updates, we decide that our ZF1 application should be installed in the `vendor` directory.

### Using Gitmodules

As said, the easiest way would be to use Gitmodules.

    $ git submodule add https://github.com/in2it/zfdemo.git vendor/zfdemo

### Using Subversion

Alternatively we can use Subversion

    $ svn co https://github.com/in2it/zfdemo/trunk vendor/zfdemo

**NOTE:** This requires often manual updates if the source of your ZF1 changes!

### Zend Framework 1

If you're application does not come pre-installed with Zend Framework, the easiest way to include it in your project is to add it to your `composer.json` of Apigility.

Add the following to your Apigility `composer.json`:

    "zendframework/zendframework1": "1.12.5"

This should be added within the `require` segment, as displayed below.

    "require": {
        ...
        "zendframework/zendframework1": "1.12.5"
    }

Now add a symlink in your Zend Framework 1 library pointing to this repository.

    $ cd vendor/zfdemo/library
    $ ln -s ../../zendframework/zendframework1/library/Zend Zend
    $ cd ../../.. (application root)

With this setup, you now have the ZF1 library autoloaded

### Custom libraries

If your application uses 3rd-party libraries or custom libraries, you need to see if they exists as Composer packages or if they are available through SCM (GIT or Subversion).

For our zfdemo, we depend on In2it library which is on GitHub, but not available as a Composer package. So for our own convenience we add them as a Gitmodule.

We need to make the exception in our `.gitignore` file to allow adding our library in, so we add the following line into our `.gitignore` file.

    !vendor/In2it

Now we can safely add the library as Gitmodule.

     $ git submodule add https://github.com/in2it/In2it.git vendor/In2it

Lastly we add a symlink in our `vendor/zfdemo/library` to point to our In2it library.

    $ cd vendor/zfdemo/library
    $ ln -s ../../In2it/library/In2it In2it
    $ cd ../../.. (application root)

## Changing APPLICATION_PATH in index

The entry point for Apigility's web interface, sets up the include paths, environments and loads the ZF2 application. But it also uses constants that are used in ZF1 which conflict when using both applications at the same time.

We therefor change the Apigility `public/index.php` file and replace `APPLICATION_PATH` into `ZF2APP_PATH`. So the `public/index.php` file looks like this:

    <?php
    /**
     * @license   http://opensource.org/licenses/BSD-3-Clause BSD-3-Clause
     * @copyright Copyright (c) 2014 Zend Technologies USA Inc. (http://www.zend.com)
     */

    /**
     * This makes our life easier when dealing with paths. Everything is relative
     * to the application root now.
     */
    chdir(dirname(__DIR__));

    // Decline static file requests back to the PHP built-in webserver
    if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
        return false;
    }

    if (!file_exists('vendor/autoload.php')) {
        throw new RuntimeException(
            'Unable to load ZF2. Run `php composer.phar install` or define a ZF2_PATH environment variable.'
        );
    }

    // Setup autoloading
    include 'vendor/autoload.php';

    if (!defined('ZF2APP_PATH')) {
        define('ZF2APP_PATH', realpath(__DIR__ . '/../'));
    }

    $appConfig = include ZF2APP_PATH . '/config/application.config.php';

    if (file_exists(ZF2APP_PATH . '/config/development.config.php')) {
        $appConfig = Zend\Stdlib\ArrayUtils::merge($appConfig, include ZF2APP_PATH . '/config/development.config.php');
    }

    // Run the application!
    Zend\Mvc\Application::init($appConfig)->run();

## Autoloading of ZF1 classes and services

Now the most challenging part of this assignment is to autoload our ZF1 classes and services, we mighte even need to make the ZF1 classes available (but we can always add them as a seperate library in `vendor` directory).

I had a discussion with the Aleksey ([@xerkus](https://twitter.com/xerkus)) and Evan ([@EvanDotPro](https://twitter.com/EvanDotPro)) of [Roave](http://roave.com) regarding the best way to bootstrap ZF1 applications in ZF2 architectures.

It seemed that Aleksey already created a [ZF2for1](https://github.com/Xerkus/zf2-for-1) bootstrapper, where they allowed ZF2 resources to become available in ZF1 applications. A reverse way of what we want to achieve.

I created a zf1to2 bootstrapper in my `zfdemo` application, which is basically the same as a vanilla ZF1 `public/index.php` file, except it doesn't contain a `run()` call on `$application->bootstrap()`. It just needs to bootstrap the application without running it.

## Getting started with Apigility

Apigility requires read/write permissions on the configuration files, so don't forget to allow write access for your application if you're using a web server (like Apache or Nginx). If you run Apigility from the buildin PHP server, you won't have any issues as the user you run the app is most likely the same that owns the configuration files.

    $ chmod -R go+w config/ data/ module/

When you go the url of your Apigility project (in my case it's http://zf-apigility.local), you should see the welcome screen.

![Apigility Welcome](http://plopster.blob.core.windows.net/apigility/Apiglity_Welcome.png)

Now it's time to add endpoints. So in "Admin" -> "API's" we create an new API for "zfdemo", our demo application. Of course you can replace this with your own application name.

![New zfdemo API](http://plopster.blob.core.windows.net/apigility/Apigility_Api_Zfdemo.png)

Now we add REST endpoints to our application. To start we define an endpoint for "user" and we choose a "Code-Connect" endpoint.

![REST user endpoint](http://plopster.blob.core.windows.net/apigility/Apigility_Rest_User.png)

When we look at the "resources" tab, we see three defined files there:

* **Collection Class:** zfdemo\V1\Rest\User\UserCollection.php
* **Entity Class:** zfdemo\V1\Rest\User\UserEntity.php
* **Resource Class:** zfdemo\V1\Rest\User\UserResource.php

We only need to work with the **Resource Class** file as this is the one actually building the logic in a similar way we build it initially in our demo application.

To test our installation, we can make a GET call to http://zf-apigilty.local/v1/user and we should receive the following JSON string

    {"type":"http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html","title":"Method Not Allowed","status":405,"detail":"The GET method has not been defined for collections"}

### Modifying Resource Class

As this resource is created automatically by Apigility, it might be you need to modify permissions before you can edit and save your changes.

    $ sudo chmod go+w module/zfdemo/src/zfdemo/V1/Rest/User/UserResource.php

To continue, the easiest way to start is to see if you can fetch a collection of user entities and fetch a single entity of this user by providing an ID.

So modifying `fetch()` and `fetchAll()` methods in `UserResource` would allow us to do this.

    /**
     * Fetch a resource
     *
     * @param  mixed $id
     * @return ApiProblem|mixed
     */
    public function fetch($id)
    {
        $service = new \User_Service_User();
        $result = $service->findUserById($id);
        return $result->toArray();
    }

    /**
     * Fetch all or a subset of resources
     *
     * @param  array $params
     * @return ApiProblem|mixed
     */
    public function fetchAll($params = array())
    {
        $service = new \User_Service_User();
        $result = $service->getAllUsers();
        return $result->toArray();
    }

So when we access [http://zf-apigility.local/v1/user/922](http://zf-apigility.local/v1/user/992) we receive the following result.

    {"id":922,"name":"Devan Armstrong","email":"destiney.parker@yahoo.com","password":"galbibtlrvp","created":"1998-04-10 23:53:31","modified":"1993-10-18 04:29:35","_links":{"self":{"href":"http:\/\/zf-apigility.local\/v1\/user\/922"}}}

**NOTE:** The reason we return our objects as arrays is that if we use our objects, Apigility returns our ID as `\u0000*\u0000_id":922`, which doesn't work well.