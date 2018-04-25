<?php
/**
 * Created by PhpStorm.
 * User: he
 * Date: 18-4-24
 * Time: 下午5:55
 */

namespace command;

use function PHPSTORM_META\type;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TestCommand extends Command
{

    protected function configure()
    {
        $this->setName('app:test')
            ->setDescription('测试')
            ->addArgument('page', InputArgument::OPTIONAL, 'page');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Configuration();
        $page = $input->getArgument('page');
        $pageSize = 5;
        $connectionParams = ['url' => \Yaf_Application::app()->getConfig()->application->mysql];
        $conn = DriverManager::getConnection($connectionParams, $config);
        $query = $conn->createQueryBuilder();
        $query->from('user', 'u');
        $pageQuery = clone $query;
        $pageQuery->select('COUNT(id) as num');
        $total = $pageQuery->execute()->fetch();
        // 设置每页查询数
        $query->setMaxResults($pageSize);
        // 设置查询开始位置
        $query->setFirstResult(($page-1)*$pageSize);
        $query->select('u.*');
        $result = $query->execute()->fetchAll();
        $data = [
            'data' => $result,
            '_page' => [
                'totalPage' => ceil(($total['num']??0)/$pageSize),
                'pageSize' => $pageSize
            ]
        ];
        print_r($data);
    }
}