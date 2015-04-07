Getting started with JRKEasyPaginationBundle
======================================

Setup
-----
JRKEasyPaginationBundle requires the Pagerfanta api folder


- Using composer

Add jrk/easypagination-bundle as a dependency in your project's composer.json file:

```
{
    "require": {
        "jrk/easypagination-bundle": "dev-master"
    }
}
```
Update composer
```
php composer update
or 
php composer.phar update


- Add JRKEasyPaginationBundle to your application kernel

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new JRK\EasyPaginationBundle\JRKEasyPaginationBundle(),
    );
}
```


Usage
-----


 - Using service

Open your controller and call the service.

``` php
<?php
    list($entities, $counter, $pager, $pagerHtml) =  $this->get('jrk_easypagination')->paginate(
        $queryBuilder,      // QueryBuilder
        array('args' => array('limit' => $limit), 'route' => self::$route_list),  // Route for HTML widget
        $limit,     // Number of items per page
        false,      // OutOfRangeException quiet or not
        $page,      // The page number (by default it will check for "page" attribute in the request)
        true        // If you need the HTML widget
    );
?>
```
