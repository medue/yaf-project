<?php
/**
 * Created by PhpStorm.
 * User: he
 * Date: 18-4-13
 * Time: 上午9:14
 */

namespace Service;


class Mq
{

    public $host;
    public $port;
    public $user;
    public $pass;
    public $vhost;

    public function getConnction()
    {
        return "this rabbitmq connection method";
    }

    public function getParams()
    {
        return [
            'host' => $this->host,
            'port' => $this->port,
            'user' => $this->user,
            'pass' => $this->pass,
            'vhost' => $this->vhost,
        ];
    }
}