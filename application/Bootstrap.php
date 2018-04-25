<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class Bootstrap extends Yaf_Bootstrap_Abstract
{
    /**
     * 加载第三方composer包
     * @param Yaf_Dispatcher $dispatcher
     */
    public function _initVendor(Yaf_Dispatcher $dispatcher)
    {
        require __DIR__.'/../vendor/autoload.php';
    }

    public function __initDoctrine(Yaf_Dispatcher $dispatcher)
    {
        $paths = array("/path/to/entity-files");
        $isDevMode = false;
        $dbParams = array(
            'driver'   => 'pdo_mysql',
            'user'     => 'root',
            'password' => 'root',
            'dbname'   => 'blog',
        );

        $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
        $entityManager = EntityManager::create($dbParams, $config);
        $result = $entityManager->createQueryBuilder()->select('*')->from('user', 'u')->getQuery()->getArrayResult();
        var_dump($result);exit;
    }
}
