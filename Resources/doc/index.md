Getting started with JRKEasyPaginationBundle
======================================

Setup
-----
JRKEasyPaginationBundle requires the ATOS api folder


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
```

Or add to your deps

```
[JRKEasyPaginationBundle]
    git=git://github.com/jreziga/JRKEasyPaginationBundle.git
    target=bundles/JRK/EasyPaginationBundle
```

... and run php bin/vendors install

... and add the JRK namespace to autoloader

``` php
<?php
   // app/autoload.php
   $loader->registerNamespaces(array(
    // ...
    'JRK' => __DIR__.'/../vendor/bundles',
  ));
```

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


- Yml configuration

``` yml
# app/config/config.yml
jrk_easy_pagination:
    files:
        pagination_pathfile: "%kernel.root_dir%/config/pagination/param/pathfile"
        pagination_request: "%kernel.root_dir%/config/pagination/bin/static/request"
        pagination_response: "%kernel.root_dir%/config/pagination/bin/static/response"
        pagination_logs: "%kernel.root_dir%/logs/pagination.log"
    params:
        pagination_merchant_id: "XXXXXXXXXXXXXXXXXX"
        pagination_currency_code: "EUR"   # OR use the currency_code provided by ATOS (978=EUR for example)
        pagination_language: "fr"
        pagination_easy_means: "CB,2,VISA,2,MASTERCARD,2"
        pagination_header_flag: "yes"
        pagination_merchant_country: "fr"
    links:
        pagination_cancel_return_url: "my_homepage_route"     # Route to redirect if the easy is canceled
        pagination_route_response: "my_pagination_response"         # Route to redirect if the easy is accepted
```

- Routes import

``` yml
# app/config/routing.yml
jrk_easy_pagination:
    resource: "@JRKEasyPaginationBundle/Resources/config/routing.yml"
    prefix: /easy
```

- Console usage 

> Install assets
``` 
php app/console assets:install
```
> Specify param's path directory (by default use [app/config/pagination/param])
``` 
php app/console jrk:pagination:install
```

For example, with default values of the bundle, you can extract the API like this:

    .
    |-- app
    |   `-- config
    |       `-- pagination
    |       `-- bin
    |           `-- static
    |              `-- request
    |              `-- response
    |       `-- param
    |           `-- certif.XXXXXXXXXXXX
    |           `-- parmcom.XXXXXXXXXXXX
    |           `-- parmcom.mercanet        # if you are using mercanet for example
    |           `-- pathfile                # generated
    |       `-- Version.txt




Usage
-----


 - Using service

Open your controller and call the service.

``` php
<?php
    $pagination_form =  $this->get('jrk_easypagination')->get_pagination_request(array("amount"=>10),MyTransactionEntity);
?>
```

Then you can use this method in your "pagination_route_response" controller

``` php
<?php
    $order = $this->get('jrk_easypagination')->pagination_load_entity();
    
    // Store the validated order in database for example
    $em = $this->getEntityManager();
    $em->persist($item);
    $em->flush();
?>
```

Controller example

``` php
<?php
    class MyController
    {

        public function easypageAction()
        {
    
            // Initialize your order entity or whatever you want
            $order = new OrderExample();
            
         
            // Don't forget to set an amount in array
            // You can dynamically override config parameters here like currency_code etc...
            $pagination_form = $this->get('jrk_easypagination')->get_pagination_request(array("amount"=>$order->getAmount()),$order);
    
    
            // Render your easy page, you can render the pagination form like that for twig : {{ pagination_form }}
            return $this->render('ShopFrontBundle:MyController:easypage.html.twig',array("pagination_form"=>$pagination_form));
    
        }
    
    
        // Controller set in your config.yml : my_pagination_response parameter
        public function my_pagination_responseAction()
        {
            $order = $this->get('jrk_easypagination')->pagination_load_entity();
            
            // Store your transaction entity in database for example, or attributes.
            $order->setState("ACCEPTED");
            $em = $this->getEntityManager();
            $em->persist($order);
            $em->flush();
            
            // Notify the user by mail for example
            /* ... */
            
            // Redirect the user in his history orders for example
            return $this->redirect($this->generateUrl("user_history_orders"));
        }
    }
?>
```
