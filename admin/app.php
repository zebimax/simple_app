<?php
/**
 * Created by PhpStorm.
 * User: Zebimax
 * Date: 25.02.15
 * Time: 16:04
 */
/**
 *  Admin project entry point.
 */
/**
 *  Root dir of main application classes.
 */
define('MVC_APP_PATH', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app');
/**
 *  Root dir of project that uses main application classes.
 */
define('ADMIN_PATH', MVC_APP_PATH . DIRECTORY_SEPARATOR . 'Admin' . DIRECTORY_SEPARATOR);
/**
 *  Dir of project views.
 */
define('MVC_VIEW_DIR' , ADMIN_PATH . 'Views' . DIRECTORY_SEPARATOR);
/**
 *  Dir of project js files for dynamic inclusion in layout for concrete action.
 */
define('MVC_JS_DIR' , ADMIN_PATH . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR);
/**
 *  Dir of project layouts.
 */
define('MVC_LAYOUTS_DIR' , ADMIN_PATH . 'Layouts' . DIRECTORY_SEPARATOR);
/**
 *  If project root in subfolder of main application folder
 *  and has own namespace this constant must be define as
 * / + ProjectNamespace, empty string '' otherwise , used for static content mostly.
 */
define('MVC_APP_NAMESPACE_FOLDER', '/Admin');

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
    require ADMIN_PATH . 'config' . DIRECTORY_SEPARATOR . 'app_config.php'
);
if (file_exists(ADMIN_PATH . 'config' . DIRECTORY_SEPARATOR . APP_ENV . '.php')) {
    Macaw::setKey('config', include ADMIN_PATH. 'config' . DIRECTORY_SEPARATOR . APP_ENV . '.php');
}
Macaw::$ulrBase = '';


require('includes/application_top.php');
define('MVC_IMAGE_DIR', '../' . DIR_WS_IMAGES);
define('ADMIN_INCLUDES_DIR', '../' . DIR_WS_INCLUDES);
\Macaw::setKey('message_stack', $messageStack);
\Macaw::setKey('php_self', 'app.php');

Macaw::get('/admin/app.php/expensive-unipharma', function() {
    $translateController = new \Admin\Controller\ExpensiveUnipharmaController();
    $translateController->indexAction();
});
Macaw::get('/admin/app.php', function() {
    $queryString = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']
        ? '?' . $_SERVER['QUERY_STRING'] :'';
    header('location:https://dod.local/admin/index.php' . $queryString);
});
Macaw::get('/admin/app.php/orders.php', function() {
    $queryString = isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']
        ? '?' . $_SERVER['QUERY_STRING'] :'';
    header('location:https://dod.local/admin/orders.php' . $queryString);
});

Macaw::post('/admin/app.php/expensive-unipharma', function() {
    $translateController = new \Admin\Controller\ExpensiveUnipharmaController();
    $translateController->getListAction();
});
Macaw::dispatch();