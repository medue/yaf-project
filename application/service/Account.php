<?php
/**
 * Created by PhpStorm.
 * User: he
 * Date: 18-4-13
 * Time: 上午9:49
 */

namespace Service;

class Account
{
    /**
     * @var \Service\Mq
     */
    public $mq;

    public function getParams()
    {
        return $this->mq->getParams();
    }

}