<?php

/**
 * @link      https://github.com/phly/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012-2020 Matthew Weier O'Phinney (https://mwop.net)
 * @license   https://github.com/phly/PhlySimplePage/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhlySimplePage;

use Psr\Container\ContainerInterface;

class PageCacheListenerFactory
{
    public function __invoke(ContainerInterface $container): PageCacheListener
    {
        return new PageCacheListener(
            $container->get(__NAMESPACE__ . '\PageCache')
        );
    }
}
