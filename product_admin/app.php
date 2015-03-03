<?php
/**
 *  ProductAdmin project entry point.
 */
/**
 *  Root dir of main application classes.
 */
define('MVC_APP_PATH', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app');
/**
 *  Root dir of project that uses main application classes.
 */
define('PRODUCT_ADMIN_PATH', MVC_APP_PATH . DIRECTORY_SEPARATOR . 'ProductAdmin' . DIRECTORY_SEPARATOR);
/**
 *  Dir of project views.
 */
define('MVC_VIEW_DIR' , PRODUCT_ADMIN_PATH . 'Views' . DIRECTORY_SEPARATOR);
/**
 *  Dir of project js files for dynamic inclusion in layout for concrete action.
 */
define('MVC_JS_DIR' , PRODUCT_ADMIN_PATH . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR);
/**
 *  Dir of project layouts.
 */
define('MVC_LAYOUTS_DIR' , PRODUCT_ADMIN_PATH . 'Layouts' . DIRECTORY_SEPARATOR);
/**
 *  If project root in subfolder of main application folder
 *  and has own namespace this constant must be define as
 * / + ProjectNamespace, empty string '' otherwise , used for static content mostly.
 */
define('MVC_APP_NAMESPACE_FOLDER', '/ProductAdmin');


if (!defined('APP_ENV')) {
    $appEnv = getenv('APP_ENV') ? getenv('APP_ENV') : 'production';
    define('APP_ENV', $appEnv);
}
define('STATIC_PREPEND', APP_ENV == 'production' ? '//static.deonlinedrogist.nl/' : '');
require(MVC_APP_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'ClassLoader.php');

$loader = require_once MVC_APP_PATH .'/vendor/autoload.php';

// register classes with namespaces
$loader->add('', MVC_APP_PATH);

Macaw::setKey(
    'app_config',
    require PRODUCT_ADMIN_PATH . 'config' . DIRECTORY_SEPARATOR . 'app_config.php'
);
if (file_exists(PRODUCT_ADMIN_PATH . 'config' . DIRECTORY_SEPARATOR . APP_ENV . '.php')) {
    Macaw::setKey('config', include PRODUCT_ADMIN_PATH. 'config' . DIRECTORY_SEPARATOR . APP_ENV . '.php');
}
Macaw::$ulrBase = '';
Macaw::get('/productadmin/app.php/translate', function() {
    $translateController = new \ProductAdmin\Controller\TranslateController();
    $translateController->indexAction();
});

Macaw::post('/productadmin/app.php/translate', function() {
    $translateController = new \ProductAdmin\Controller\TranslateController();
    $translateController->getListAction();
});

Macaw::post('/productadmin/app.php/translate/save', function() {
    $translateController = new \ProductAdmin\Controller\TranslateController();
    $translateController->saveAction();
});

Macaw::dispatch();