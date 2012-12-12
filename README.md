# PhlySimplePage

A ZF2 module for "static" pages.

## Overview

In most ZF2 applications, you'll have at least a few pages that are basically
static -- the controller contains no logic for the given endpoint, and it 
simply renders a template.

By default, this requires the following steps:

- Create a route
- Create a controller (if you don't have one already)
- Create an action in that controller
- Create a template

This module halves the workflow by eliminating the middle two steps.

## Installation

### Source download

Grab a source download:

- https://github.com/weierophinney/PhlySimplePage/archive/master.zip

Unzip it in your `vendor` directory, and rename the resulting directory:

```sh
cd vendor
unzip /path/to/PhlySimplePage-master.zip
mv PhlySimplePage-master PhlySimplePage
```

### Git submodule

Add the repository as a git submodule in your project.

```sh
git submodule add git://github.com/weierophinney/PhlySimplePage.git vendor/PhlySimplePage
```

### Use Composer

Assuming you already have `composer.phar`, add `PhlySimplePage` to your
`composer.json` file:

```js
{
    "require": {
        "phly/phly-simple-page": "dev-master"
    }
}
```

And then install:

```sh
php composer.phar install
```

## Enable the module

Once you've installed the module, you need to enable it. You can do this by 
adding it to your `config/application.config.php` file:

```php
<?php
return array(
    'modules' => array(
        'Application',
        'PhlySimplePage',
    ),
);
```

## Usage

Create configuration in your application, mapping a route to the controller
`PhlySimplePage\Controller\Page`, and specifying a `template` key in the route
defaults.

```php
return array(
    'router' => array(
        'routes' => array(
            'about' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/about',
                    'defaults' => array(
                        'controller' => 'PhlySimplePage\Controller\Page',
                        'template'   => 'application/pages/about',
                    ),
                ),
            ),
        ),
    ),
);
```

The, make sure you create a template for the page. In the above example, I'd 
likely create the file in `module/Application/view/application/pages/about.phtml`.

## TODO

- Add listener(s) to allow caching of rendered static pages
