# PhlySimplePage

A Laminas MVC module for "static" pages.

## Overview

In most Laminas MVC applications, you'll have at least a few pages that are
basically static — the controller contains no logic for the given endpoint, and
it simply renders a template.

By default, this requires the following steps:

- Create a route
- Create a controller (if you don't have one already)
- Create an action in that controller
- Create a template

This module halves the workflow by eliminating the middle two steps.

## Installation

Use [Composer](https://getcomposer.org):

```console
$ composer require phly/phly-simple-page
```

## Enable the module

If you are using [laminas-component-installer](https://docs.laminas.dev/laminas-component-installer),
you will get prompted to add the module to your `config/application.config.php`
file.

If you are not, or you choose not to use the component installer, you can enable
it by adding manually it to your `config/application.config.php` file:

```php
<?php
return [
    'modules' => [
        'PhlySimplePage',
        'Application',
    ],
];
```

## Usage

Create configuration in your application, mapping a route to the controller
`PhlySimplePage\PageController`, and specifying a `template` key in the route
defaults.

```php
use PhlySimplePage\PageController;

return [
    'router' => [
        'routes' => [
            'about' => [
                'type' => 'Literal',
                'options' => [
                    'route' => '/about',
                    'defaults' => [
                        'controller' => PageController::class,
                        'template'   => 'application/pages/about',
                        // optionally set a specific layout for this page
                        'layout'     => 'layout/some-layout',
                    ],
                ],
            ],
        ],
    ],
];
```

Then, make sure you create a template for the page. In the above example, I'd 
likely create the file in `module/Application/view/application/pages/about.phtml`.

## Caching

You can enable a write-through cache for all pages served by the
`PageController`. This is done via the following steps:

- Creating cache configuration
- Enabling the page cache factory

To create cache configuration, create a `phly-simple-page` configuration key in
your configuration, with a `cache` subkey, and configuration suitable for
`Laminas\Cache\StorageFactory::factory`. As an example, the following would setup
filesystem caching:

```php
return [
    'phly-simple-page' => [
        'cache' => [
            'adapter' => [
                'name'   => 'filesystem',
                'options' => [
                    'namespace'       => 'pages',
                    'cache_dir'       => getcwd() . '/data/cache',
                    'dir_permission'  => '0777',
                    'file_permission' => '0666',
                ],
            ],
        ],
    ],
];
```

To enable the page cache factory, do the following:

```php
return [
    'service_manager' => [
        'factories' => [
            'PhlySimplePage\PageCache' => \PhlySimplePage\PageCacheFactory::class,
        ],
    ],
];
```

### Selectively disabling caching for given routes

If you do **not** want to cache a specific page/route, you can disable it by
adding the default key `do_not_cache` with a boolean `true` value to the route.
As an example:

```php
'about' => [
    'type' => 'Literal',
    'options' => [
        'route' => '/about',
        'defaults' => [
            'controller'   => \PhlySimplePage\PageController::class,
            'template'     => 'application/pages/about',
            'do_not_cache' => true,
        ],
    ],
],
```

### Clearing the cache

To clear the cache for any given page, or for all pages, your cache adapter (a)
must support cache removal from the command line (APC, ZendServer, and several
other adapters do not), and (b) must support flushing if you wish to clear all
page caches at once.

The module provides a vendor binary, `phly-simple-page` for accomplishing this:

- `./vendor/bin/phly-simple-page clear:cache` will clear all cached pages at
  once.

- `./vendor/bin/phly-simple-page clear:cache --page=` clear a single
  cached page; use the template name you used in the routing configuration as
  the page value.

## TODO

- Ability to clear sets of pages
