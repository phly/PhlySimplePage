<?php
/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePageTest;

use Laminas\Mvc\Router\RouteMatch as LegacyRouteMatch;
use Laminas\Router\RouteMatch;

trait RouteMatchCreationTrait
{
    public function createRouteMatch($matches = [])
    {
        return class_exists(LegacyRouteMatch::class)
            ? new LegacyRouteMatch($matches)
            : new RouteMatch($matches);
    }
}
