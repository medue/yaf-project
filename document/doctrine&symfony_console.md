### yaf使用doctrine和symfony的console

* 安装doctrine
```bash
# doctrine安装会自动安装symfony的console组件
composer require doctrine/orm
```

#### console使用

通过路径定位command文件夹，用php反射来将自定义的命令类放到console的Application
参考bin/console

* 注册命名空间
```json
"autoload": {
    "psr-4": {
        "command\\": "application/command/"
    }
}
```

* 添加console（命令行入口）文件

```php
#!/usr/bin/env php
<?php

set_time_limit(0);

define("APP_PATH",  realpath(dirname(__FILE__) . '/../'));
define("APPLICATION_CONFIG_PATH", APP_PATH . '/conf');

require __DIR__.'/../vendor/autoload.php';
require_once APPLICATION_CONFIG_PATH . '/env.php';
use Symfony\Component\Console\Application;

$app = new Yaf_Application(APPLICATION_CONFIG_PATH . '/application.ini', APPLICATION_ENV);
$app->bootstrap();

$commandPath = $app->getConfig()->application->directory.'command';
$application = new Application();
$app_list = [];

foreach (glob($commandPath.'/*Command.php') as $row){
    $class = sprintf('command\\%s', basename($row, '.php'));
    $object = new \ReflectionClass($class);
    $app_list[] = $object->newInstance();
}

$application->addCommands($app_list);
$application->setAutoExit(false);
$application->run();
```

* 添加Bootstrap.php

```php
<?php

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
}

```

* 测试

application/command/TestCommand.php

```php
<?php
/**
 * Created by PhpStorm.
 * User: he
 * Date: 18-4-24
 * Time: 下午5:55
 */

namespace command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class TestCommand extends Command
{

    protected function configure()
    {
        $this->setName('app:test')
            ->setDescription('测试')
            ->addArgument('param', InputArgument::OPTIONAL, '参数');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        print_r($input->getArguments());
    }

}
```

```bash
php bin/console app:test test

# 返回值
Array
(
    [command] => app:test
    [param] => test
)
```

[更多console用法参考console组件文档，点击查看文档](https://github.com/symfony/console)


#### doctrine使用

[doctrine官网](https://www.doctrine-project.org)

不建议在这里使用doctrine，会拖慢yaf的运行速度

要使用的话可以通过服务容器和依赖注入把doctrine连接对象放到服务容器里面，以便在项目中随时调用

这里就用上面的test command来做演示

* 添加配置

这里我用的是url连接模式

conf/application.ini
```ini
application.mysql=mysql://root:root@127.0.0.1:3306/blog
```

* 演示
```php
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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;

class TestCommand extends Command
{

    protected function configure()
    {
        $this->setName('app:test')
            ->setDescription('测试');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Configuration();
        $connectionParams = ['url' => \Yaf_Application::app()->getConfig()->application->mysql];
        $conn = DriverManager::getConnection($connectionParams, $config);
        $query = $conn->createQueryBuilder();
        $query->select('u.*');
        $query->from('user', 'u');
        $query->where('u.id = 1');
        $result = $query->execute()->fetch();
        print_r($result);exit;
    }
}

# 结果
/*
Array
(
    [id] => 1
    [full_name] => 
    [username] => jack
    [email] => 666555@gmail.com
    [password] => 123456
    [roles] => ROLE_ADMIN
)
*/
```

* 事务

application/command/TestCommand.php
```php
protected function execute(InputInterface $input, OutputInterface $output)
{
    $config = new Configuration();
    $connectionParams = ['url' => \Yaf_Application::app()->getConfig()->application->mysql];
    $conn = DriverManager::getConnection($connectionParams, $config);
    $conn->beginTransaction();
    try{
        $query = $conn->createQueryBuilder();
        $query->insert('user')->values(['username' => ':username', 'roles' => ':roles']);
        $query->setParameter('username', 'tom');
        $query->setParameter('roles', 'ROLE_BUYER');
        $result = $query->execute();
        $lastId = $conn->lastInsertId();
        if(1 === $result){
            /*...*/
        }
        $conn->commit();
    }catch (\Exception $e){

        $conn->rollBack();
    }
}
```

* 分页

这个页可以用doctrine分页工具类Paginator来实现分页

[https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/tutorials/pagination.html](https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/tutorials/pagination.html)

```php
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
```

测试
```bash
php bin/console app:test 1
```

结果
```
Array
(
    [data] => Array
        (
            [0] => Array
                (
                    [id] => 1
                    [full_name] => 
                    [username] => jack
                    [email] => 666555@gmail.com
                    [password] => 123456
                    [roles] => ROLE_ADMIN
                )

            [1] => Array
                (
                    [id] => 2
                    [full_name] => 
                    [username] => tom
                    [email] => 
                    [password] => 
                    [roles] => ROLE_BUYER
                )

            [2] => Array
                (
                    [id] => 3
                    [full_name] => 
                    [username] => tom
                    [email] => 
                    [password] => 
                    [roles] => ROLE_BUYER
                )

            [3] => Array
                (
                    [id] => 4
                    [full_name] => 
                    [username] => tom
                    [email] => 
                    [password] => 
                    [roles] => ROLE_BUYER
                )

            [4] => Array
                (
                    [id] => 5
                    [full_name] => 
                    [username] => tom
                    [email] => 
                    [password] => 
                    [roles] => ROLE_BUYER
                )

        )

    [_page] => Array
        (
            [totalPage] => 3
            [pageSize] => 5
        )

)

```





