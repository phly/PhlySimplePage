<?php
/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2012 Matthew Weier O'Phinney (http://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePage;

use RuntimeException;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheAdapter;
use Zend\Cache\Storage\FlushableInterface;
use Zend\Console\Adapter\AdapterInterface as Console;
use Zend\Console\ColorInterface as Color;
use Zend\Console\Request as ConsoleRequest;
use Zend\Mvc\Controller\AbstractActionController;

class CacheController extends AbstractActionController
{
    protected $cache;

    protected $console;

    public function setCache(CacheAdapter $cache)
    {
        $this->cache = $cache;
    }

    public function setConsole(Console $console)
    {
        $this->console = $console;
    }

    public function clearAllAction()
    {
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException(sprintf(
                '%s can only be run from the console',
                __METHOD__
            ));
        }

        $this->console->writeLine('Clearing caches for all static pages', Color::BLUE);

        if (!$this->cache instanceof FlushableInterface) {
            $this->console->writeLine('Cache does not support flushing!', Color::RED);
            return;
        }

        $this->cache->flush();

        $this->console->writeLine('Cache operation complete', Color::GREEN);
    }

    public function clearOneAction()
    {
        $request = $this->getRequest();
        if (!$request instanceof ConsoleRequest) {
            throw new RuntimeException(sprintf(
                '%s can only be run from the console',
                __METHOD__
            ));
        }

        $page = $this->params()->fromRoute('page', false);
        if (!$page) {
            $this->console->writeLine('No page provided', Color::GREEN);
            return;
        }

        $this->console->writeLine(sprintf('Clearing cache for page "%s"', $page), Color::BLUE);

        $key = Module::normalizeCacheKey($page);
        if (!$this->cache->hasItem($key)) {
            $this->console->writeLine('Page is not in cache', Color::GREEN);
            return;
        }

        $this->cache->removeItem($key);

        $this->console->writeLine('Cache operation complete', Color::GREEN);
    }
}
