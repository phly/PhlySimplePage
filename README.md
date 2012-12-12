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
- Add API documentation
- Add travis-ci build info
- Add to packagist
