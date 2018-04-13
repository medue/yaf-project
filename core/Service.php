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