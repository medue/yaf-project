<?php

class IndexController extends Yaf_Controller_Abstract
{
    public function indexAction()
    {
        $this->getView()->display('index/index.phtml', ['time' => time(), 'action' => __METHOD__, 'dir' => __DIR__]);
    }
}