<?php
namespace Controller;

use PluginManager;
use Plugins\Controller\Colors\ColorsInterface;

class AbstractConsoleController
{
    protected $params = array();

    protected $route = 'index';
    /**
     * @var ColorsInterface
     */
    private $colors;

    /** @var PluginManager */
    protected $pluginManager;

    public function __construct()
    {
        $this->setRoute(array_shift($_SERVER['argv']));
        $this->setParams($_SERVER['argv']);
        $this->initPlugins();
    }

    /**
     * @param ColorsInterface $colors
     * @return $this
     */
    public function setColors(ColorsInterface $colors)
    {
        $this->colors = $colors;
        return $this;
    }

    protected function setParams($dirtyParams)
    {
        if (is_array($dirtyParams) && !empty($dirtyParams)) {
            foreach ($dirtyParams as $dirtyParam) {
                if (strrpos($dirtyParam, '--') === 0) {
                    $param = explode('=', $dirtyParam);
                    $this->params[trim(str_replace('--', '', $param[0]))] = trim($param[1]);
                }
            }
        }
    }

    protected function setRoute($route)
    {
        if ($route) {
            $this->route = $route;
        }
    }

    protected function getParam($key, $default = null)
    {
        if (isset($this->params[$key])) {
            $default = $this->params[$key];
        }
        return $default;
    }

    protected function getColorizedString($string, $foreGroundColor = null, $backGroundColor = null)
    {
        if (!$this->colors) {
            $this->colors = $this->pluginManager->get('cliColors');
        }
        return $this->colors->getColoredString($string, $foreGroundColor, $backGroundColor);
    }

    private function initPlugins()
    {
        $this->pluginManager = new PluginManager(\Macaw::getConfig('plugins'), array());
    }
}