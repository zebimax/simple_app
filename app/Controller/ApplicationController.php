<?php

namespace Controller;

use Application\Result;
use Controller;
use Model\AbstractModel;
use Model\ModelMysql;
use Model\Mysql\ProductsDescription;
use Model\Solarium\Solarium;
use PluginManager;

/**
 * Class ApplicationController
 * @package Controller
 * @method mixed defaultValue(array $array = array(), string $key, $default = null)
 * @method mixed paginationLinks($count, $current, $byPage, $pageLinks = 5)
 * @method mixed linkMaker(array $params = array())
 */
class ApplicationController extends Controller
{
    protected $skin = 'main';

    protected $request_type;

    protected $old_php_self;

    protected $mobileUrl = '';

    protected $mobileUrlBase = 'https://m.deonlinedrogist.nl/';

    protected $skinBox;

    protected $view;

    protected $viewDir;

    protected $action;

    /** @var PluginManager */
    protected $pluginManager;

    protected $title = '';
    protected $description;
    protected $keyw;
    protected $canonicalUrl = '';
    protected $pageJs = '';
    protected $languageId;
    protected $name;
    protected $models = array();
    protected $skinId;
    protected $limit;
    protected $request = array();

    protected $langCode = 'nl';

    protected $breadcrumbTrail = array();

    protected $langsCodesMap = array(
        4 => 'nl',
        5 => 'en'
    );

    protected $defaultLimit = 10;
    protected $maxLimit = 1000;

    public function preDispatch()
    {
        $this->request = array_merge($_GET, $_POST);
        $this->initPlugins();
        $this->initViews();
        $this->initPageJs();
        $this->request_type = \Macaw::getKey('request_type');
        $this->old_php_self = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $this->modifyMobileUrl();
        $this->languageId = \Macaw::getKey('languages_id');
        $this->detectSkin();
        $this->limit = (int)$this->defaultValue($this->request, 'limit');
        $this->setLangCode();
    }

    protected function detectSkin()
    {
        if (\Macaw::getKey('header_variant') !== 'new') {
            $this->skin = 'classic';
        }
        $this->skinId = \Macaw::getKey('skin');
    }

    /**
     * @param $method
     * @param $params
     * @return mixed
     */
    public function __call($method, $params)
    {
        $plugin = $this->plugin($method);
        if (is_callable($plugin)) {
            return call_user_func_array($plugin, $params);
        }
        return $plugin;
    }

    /**
     * @param $name
     * @param array $options
     * @return mixed
     */
    public function plugin($name, array $options = null)
    {
        return $this->pluginManager->get($name, $options);
    }

    /**
     * @return mixed
     */
    public function getOldPhpSelf()
    {
        return $this->old_php_self;
    }

    protected function modifyMobileUrl()
    {
        $this->mobileUrl = $this->mobileUrlBase;
    }

    /**
     *  Directory of views defines by the short name of controller class
     * without 'Controller' part, e.g. for BaseNamespace/MainController view dir will be
     * MVC_VIEW_DIR / main
     */
    protected function initViews()
    {
        $classParts = explode('\\', get_class($this));
        $this->name = strtolower(str_replace('Controller' , '', end($classParts)));
        $this->viewDir = MVC_VIEW_DIR . $this->name;
        $this->view = $this->viewDir . DIRECTORY_SEPARATOR . $this->action . '.phtml';
    }

    protected function getLayout()
    {
        return 'main';
    }

    /**
     * Get canonical URL for current page
     * Currently used for categories / brand / special / new
     *
     * @return string meta tag <link rel='canonical' ...
     */
    protected function canonicalUrl()
    {
        if (preg_match(
            '~^(.*?)\?.*$~',
            $_SERVER['REQUEST_URI'],
            $pregCanonicalArr)
        ) {
            $getParams = array();
            if (isset($_GET['c']) && $_GET['c']!=='') {
                $getParams['c'] = 'c=' . $_GET['c'];
            }

            if (isset($_GET['m']) && $_GET['m']!=='') {
                $getParams['m'] = 'm='.$_GET['m'];
            }

            if (isset($_GET['tab']) && $_GET['tab']=='brand') {
                $getParams['tab'] = 'tab=' . $_GET['tab'];
            }

            if (count($getParams)) {
                $getParams = '?' . implode('&', $getParams);
            } else {
                $getParams = '';
            }

            $this->canonicalUrl = sprintf(
                '<link rel="canonical" href="%s">',
                HTTPS_SERVER . $pregCanonicalArr[1] . $getParams
            );
        }
    }

    protected function initPageJs()
    {
        if (file_exists(MVC_JS_DIR . $this->name . DIRECTORY_SEPARATOR . $this->action . '.js')) {
            $this->pageJs = sprintf(
                '<script type="text/javascript" src="%s"></script>',
                STATIC_PREPEND . '/app' . MVC_APP_NAMESPACE_FOLDER . '/public/js/' . $this->name . DIRECTORY_SEPARATOR . $this->action . '.js'
            );
        };
    }

    /**
     * @param $modelName
     * @param string $type
     * @return AbstractModel
     */
    protected function getModel($modelName, $type = AbstractModel::MYSQL)
    {
        if (!isset($this->models[$modelName])) {
            switch ($type) {
                case AbstractModel::MYSQL:
                    $this->models[$modelName] = $this->getMysqlModel($modelName);
                    break;
                case AbstractModel::SOLARIUM:
                    $this->models[$modelName] = $this->getSolariumModel($modelName);
                    break;
                default:
                    $this->models[$modelName] = $this->getMysqlModel($modelName);
                    break;
            }
        }
        return $this->models[$modelName];
    }

    /**
     * @param $modelName
     * @return ModelMysql
     */
    protected function getMysqlModel($modelName)
    {
        $modelDir = 'Model\Mysql\\';
        $model = null;
        if (class_exists($modelDir . $modelName)) {
            $class = $modelDir . $modelName;
            $model = new $class(\MysqlDb::getInstance(\Macaw::getConfig('mysql')));
        }
        return $model;
    }

    /**
     * @param $modelName
     * @return Solarium
     */
    protected function getSolariumModel($modelName)
    {
        $modelDir = 'Model\Solarium\\';
        $model = null;
        if (class_exists($modelDir . $modelName)) {
            $class = $modelDir . $modelName;
            $model = new $class($this->langCode);
        }
        return $model;
    }

    /**
     * @param Result $result
     */
    protected function renderJson(Result $result)
    {
        $data = $result->getData();
        $success = $result->getSuccess();
        $message = $result->getMessage();
        require MVC_VIEW_DIR . 'json/json.phtml';
    }

    protected function setLangCode()
    {
        if (isset($this->langsCodesMap[$this->languageId])) {
            $this->langCode = $this->langsCodesMap[$this->languageId];
        }
    }

    /** @return ProductsDescription */
    protected function getProductsDescriptionModel()
    {
        return $this->getModel('ProductsDescription');
    }

    /**
     * @return int
     */
    protected function getLimit()
    {
        return ($this->limit > 0 && $this->limit <= $this->maxLimit) ? $this->limit : $this->defaultLimit;
    }

    /**
     * @return int
     */protected function getOffset()
    {
        $page = (int)$this->defaultValue($this->request, 'page');
        return $page > 0 ? $page - 1 : 0;
    }

    private function initPlugins()
    {
        $this->pluginManager = new PluginManager(\Macaw::getConfig('plugins'), array());
    }
}