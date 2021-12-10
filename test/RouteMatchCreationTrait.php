<?php

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
