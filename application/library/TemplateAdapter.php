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