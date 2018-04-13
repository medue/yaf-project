<?php
use Core\Service;
class IndexController extends Yaf_Controller_Abstract
{
    public function indexAction()
    {
        var_dump(Service::getInstance()->get('account')->getParams());
        $this->getView()->assign("msg", "Hello World");
    }
}