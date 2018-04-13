### yaf 服务容器、依赖注入、定位与发现

* 服务容器

参考自symfony的pimple[https://pimple.symfony.com/](https://pimple.symfony.com/)

应用程序中充满了实现各种功能的对象.
如果一个对象在多个地方实例化非常不便于管理和维护.
服务容器就是把常用的类或实例注入到第三方实体中，在使用时通过第三方实体来调用需要用到的
对象。从而降低了维护成本，也达到了解耦的效果。

初始化composer&注册命名空间
```bash
composer init
"autoload": {
    "psr-4": {
        "Service\\": "application/service/",
        "Core\\": "core/"
    }
}
```

服务配置
conf/service.ini
```ini
[account]
class="Service\\Account"
@mq="Service\\Mq"
[mq]
class="Service\\Mq"
host="localhost"
port="5672"
user="rabbit"
pass="654321"
vhost="/"
```

启用Bootstrap
public/index.php
```php
$app
    ->bootstrap()
    ->run();
```

application/Bootstrap.php
```php
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
```

core/Service.php
```php
<?php
/**
 * Created by PhpStorm.
 * User: he
 * Date: 18-4-13
 * Time: 上午9:15
 */

namespace Core;

use Yaf_Registry;

class Service
{

    private $_services = [];

    private $_definitions = [];

    private static $_instances;


    /**
     * @return Service
     */
    public static function getInstance()
    {
        if(self::$_instances){
            return self::$_instances;
        }
        return self::$_instances = new self();
    }

    /**
     * @param $id
     * @return mixed|null
     */
    public function get($id)
    {
        if(isset($this->_services[$id])){
            return $this->_services[$id];
        }
        if(isset($this->_definitions[$id])){
            return $this->_definitions[$id];
        }
        return null;
    }

    public function has($id)
    {
        if(isset($this->_services[$id])){
            return true;
        }
        if(isset($this->_definitions[$id])){
            return true;
        }
        return false;
    }

    public function set($id, $service=null)
    {
        $id = trim($id);
        if($this->has($id)){
            return;
        }
        if(isset($service['class'])){
            $reflection = new \ReflectionClass($service['class']);
            $this->_services[$id] = $reflection->newInstanceArgs();
            unset($service['class']);
            foreach ($service as $key => $row){
                $constant = trim($key);
                if($reflection->hasProperty($constant) && $reflection->getProperty($constant)->isPublic()){
                    $this->_services[$id]->{$constant} = $row;
                }
                if(strpos($constant, '@') === 0){
                    $di = lcfirst(substr($constant, 1));
                    if($this->has($di) === false){
                        $services = Yaf_Registry::get('service');
                        if(!isset($services[$di])){
                            throw new \Exception('dependency service not found');
                        }
                        $this->set($di, $services[$di]);
                    }
                    $this->_services[$id]->{substr($constant, 1)} = $this->_services[$di];
                }
            }
        }
        if($service === null){
            unset($this->_services[$id]);
        }

        if(is_object($service) || is_callable($service)){
            $this->_services[$id] = $service;
        }
    }
}
```




