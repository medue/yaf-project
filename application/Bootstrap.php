<?php

use Core\Service;

class Bootstrap extends Yaf_Bootstrap_Abstract
{

    public function _initVendor()
    {
        require_once "../vendor/autoload.php";
    }

    public function _initService()
    {
        try{
            $service_c = parse_ini_file(APPLICATION_CONFIG_PATH.'/service.ini', true);
            Yaf_Registry::set('service', $service_c);
            if(empty($service_c) || !is_array($service_c)){
                goto dn;
            }

            foreach ($service_c as $key => $row){
                if(!Service::getInstance()->has($key)){
                    Service::getInstance()->set($key, $row);
                }
            }
        }catch (\Exception $e) {

        }finally{
            unset($service_c);
        }
        dn:
    }
}
