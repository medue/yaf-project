<?php

class Bootstrap extends Yaf_Bootstrap_Abstract
{

    public function _initVendor()
    {
        require __DIR__.'/../vendor/autoload.php';
    }

    public function _initTwig(Yaf_Dispatcher $dispatcher)
    {
        $dispatcher->disableView();
        $config = Yaf_Application::app()->getConfig()->application->toArray();
        if(isset($config['twig']['enable']) && $config['twig']['enable']){
            $viewPath = isset($config['twig']['views_path'])?$config['twig']['views_path']:APP_PATH.'/application/views/';
            $dispatcher->setView(
                new TemplateAdapter($viewPath, $config)
            );
        }
    }
}