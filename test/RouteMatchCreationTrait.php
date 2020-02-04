<?php

/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlySimplePageTest;

use Laminas\Router\RouteMatch;

trait RouteMatchCreationTrait
{
    public function createRouteMatch(array $matches = []): RouteMatch
    {
        return new RouteMatch($matches);
    }
}
