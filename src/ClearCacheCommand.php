<?php
/**
 * @link      https://github.com/weierophinney/PhlySimplePage for the canonical source repository
 * @copyright Copyright (c) 2020 Matthew Weier O'Phinney (http://mwop.net)
 * @license   https://github.com/weierophinney/PhlySimplePage/blog/master/LICENSE.md New BSD License
 */

namespace PhlySimplePage;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Zend\Cache\Storage\Adapter\AbstractAdapter;
use Zend\Cache\Storage\FlushableInterface;

class ClearCacheCommand extends Command
{
    private $cache;

    public function __construct(AbstractAdapter $cache)
    {
        $this->cache = $cache;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('cache:clear');
        $this->addOption(
            'page',
            'p',
            InputOption::VALUE_REQUIRED,
            'Specific page for which to clear the cache, as matched by routing'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $page = $input->getOption('page');
        if (! $page) {
            return $this->clearAllPages($output);
        }

        return $this->clearPage($page, $output);
    }

    private function clearAllPages(OutputInterface $output)
    {
        $output->writeln('<info>Clearing caches for all static pages</info>');

        if (! $this->cache instanceof FlushableInterface) {
            $output->writeln('<error>Cache does not support flushing!</error>');
            return 1;
        }

        $this->cache->flush();

        $output->writeln('<info>Cache operation complete</info>');

        return 0;
    }

    private function clearPage($page, OutputInterface $output)
    {
        $output->writeln(sprintf('<info>Clearing cache for page "%s"</info>', $page));

        $key = Module::normalizeCacheKey($page);
        if (! $this->cache->hasItem($key)) {
            $output->writeln('<info>Page is not in cache</info>');
            return 0;
        }

        $this->cache->removeItem($key);

        $output->writeLine('<info>Cache operation complete</info>');

        return 0;
    }
}
