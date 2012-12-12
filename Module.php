<?php

namespace PhlySimplePage;

use Zend\Mvc\Application;
use Zend\Stdlib\ResponseInterface;

class Module
{
    public function getAutoloaderConfig()
    {
        return array('Zend\Loader\StandardAutoloader' => array(
            'namespaces' => array(
                __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
            ),
        ));
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap($e)
    {
        $app    = $e->getTarget();
        $events = $app->getEventManager();
        $events->attach('route', array($this, 'onRoutePost'), -100);
    }

    public function onRoutePost($e)
    {
        $matches = $e->getRouteMatch();
        if (!$matches) {
            return;
        }

        $controller = $matches->getParam('controller');
        if ($controller != 'PhlySimplePage\Controller\Page') {
            return;
        }

        $app    = $e->getTarget();
        $events = $app->getEventManager();
        $shared = $events->getSharedManager();
        $shared->attach('PhlySimplePage\PageController', 'dispatch', array($this, 'onDispatchPost'), -1);
    }

    public function onDispatchPost($e)
    {
        $target = $e->getTarget();
        if (!$target instanceof PageController) {
            return;
        }

        $error = $e->getError();
        if ($error != Application::ERROR_CONTROLLER_INVALID) {
            return;
        }

        $app     = $e->getApplication();
        $results = $app->getEventManager()->trigger('dispatch.error', $app, $e);
        $return  = $results->last();

        if ($return instanceof ResponseInterface) {
            return $return;
        }

        if ($return) {
            $e->setResult($return);
        }
    }
}
