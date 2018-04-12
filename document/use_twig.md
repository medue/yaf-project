### yaf使用twig

[project: https://github.com/medue/yaf-project/tree/master%23twig](https://github.com/medue/yaf-project/tree/master%23twig)

* install

```bash
# 安装twig
composer require "twig/twig:^2.0"
```

* add config

```ini
# 添加配置
application.twig.enable     =true   #is enable twig 
application.twig.views_path  =APP_PATH "/application/views/"    #view path
```

* add template adapter 

application/library/TemplateAdapter.php
```php
# 添加模板适配器
<?php

class TemplateAdapter implements Yaf_View_Interface
{
    /**
     * @var \Twig_Loader_Filesystem
     */
    protected $loader;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var array
     */
    protected $variables = array();

    /**
     * @param string $templateDir
     * @param array  $options
     */
    public function __construct($templateDir, array $options = array())
    {
        $this->loader = new Twig_Loader_Filesystem($templateDir);
        $this->twig   = new Twig_Environment($this->loader, $options);
    }

    /**
     * @param string $name
     * @param null $value
     * @return void
     */
    public function assign($name, $value = null)
    {
        $this->variables[$name] = $value;
    }

    /**
     * @param string $template
     * @param null $variables
     * @return void
     */
    public function display($template, $variables = null)
    {
        echo $this->render($template, $variables);
    }

    /**
     * @return array
     */
    public function getScriptPath()
    {
        return $this->loader->getPaths();
    }

    /**
     * @param string $template
     * @param array  $variables
     * @return string
     */
    public function render($template, $variables = null)
    {
        if (is_array($variables)){
            $this->variables = array_merge($this->variables, $variables);
        }

        return $this->twig->loadTemplate($template)->render($this->variables);
    }

    /**
     * @param string $templateDir
     * @return void
     */
    public function setScriptPath($templateDir)
    {
        $this->loader->setPaths($templateDir);
    }
}
```

* register twig

application/Bootstrap.php
```php
# 添加Bootstrap.php文件
<?php

class Bootstrap extends Yaf_Bootstrap_Abstract
{

    public function _initVendor()
    {
        require __DIR__.'/../vendor/autoload.php';
    }

    public function _initTwig(Yaf_Dispatcher $dispatcher)
    {
        $config = Yaf_Application::app()->getConfig()->application->toArray();
        if(isset($config['twig']['enable']) && $config['twig']['enable']){
            $dispatcher->disableView();
            $viewPath = isset($config['twig']['views_path'])?$config['twig']['views_path']:APP_PATH.'/application/views/';
            $dispatcher->setView(
                new TemplateAdapter($viewPath, $config)
            );
        }
    }
}
```

public/index.php
```php
$app
    ->bootstrap()
    ->run();
```

* test

application/controllers/Index.php indexAction
```php
$this->getView()->display('index/index.phtml', ['time' => time(), 'action' => __METHOD__, 'dir' => __DIR__]);
```

application/views/index/index.phtml
```html
<b>datetime</b>：{{ time | date('Y-m-d H:i:s') }}<br/>
<b>action</b>：{{ action }}<br/>
<b>path</b>：{{ dir }}<br/>

```
