<?php
use Controller\Console\ConsoleController;
use Controller\SearchController;
use Redirect\ManufacturerRedirect;

define('MVC_APP_PATH', __DIR__);
define('MVC_VIEW_DIR' , MVC_APP_PATH . DIRECTORY_SEPARATOR . 'Views' . DIRECTORY_SEPARATOR);
define('MVC_JS_DIR' , MVC_APP_PATH . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR);
define('MVC_LAYOUTS_DIR' , MVC_APP_PATH . DIRECTORY_SEPARATOR . 'Layouts' . DIRECTORY_SEPARATOR);
define('MVC_APP_NAMESPACE_FOLDER', '');


//define('MVC_APP_DOD', true);
if (!defined('APP_ENV')) {
    $appEnv = getenv('APP_ENV') ? getenv('APP_ENV') : 'production';
    define('APP_ENV', $appEnv);
}

define('STATIC_PREPEND', APP_ENV == 'production' ? '//static.deonlinedrogist.nl/' : '');

require(MVC_APP_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'composer' . DIRECTORY_SEPARATOR . 'ClassLoader.php');


$loader = require_once __DIR__.'/vendor/autoload.php';

// register classes with namespaces
$loader->add('', MVC_APP_PATH);


Macaw::setKey('app_config', require 'config' . DIRECTORY_SEPARATOR . 'app_config.php');
if (file_exists('config' . DIRECTORY_SEPARATOR . APP_ENV . '.php')) {
    Macaw::setKey('config', include 'config' . DIRECTORY_SEPARATOR . APP_ENV . '.php');
}


chdir(MVC_APP_PATH . DIRECTORY_SEPARATOR . '..');
require(MVC_APP_PATH . DIRECTORY_SEPARATOR . '../includes/application_top.php');

\Macaw::setKey('skin', $skin);
\Macaw::setKey('skin_box', $skin_box);
\Macaw::setKey('PHP_SELF', $PHP_SELF);
\Macaw::setKey('request_type', $request_type);
\Macaw::setKey('language', $language);
\Macaw::setKey('messageStack', $messageStack);
\Macaw::setKey('breadcrumb', $breadcrumb);
\Macaw::setKey('store', $store);
\Macaw::setKey('enable_page_cache', $enable_page_cache);
\Macaw::setKey('cart', $cart);
\Macaw::setKey('currencies', $currencies);
\Macaw::setKey('stripped_header', $stripped_header);
\Macaw::setKey('phoneClinet', $phoneClinet);
\Macaw::setKey('freeShippingNl', $freeShippingNl);
\Macaw::setKey('mobileUrl', $mobileUrl);
\Macaw::setKey('sociomantic', $sociomantic);
\Macaw::setKey('seo_urls', $seo_urls);
\Macaw::setKey('languages_id', $languages_id);
\Macaw::setKey('header_variant', $header_variant);
\Macaw::setKey('skin_page', $skin_page);
\Macaw::setKey('skin_parameters', $skin_parameters);
\Macaw::setKey('skin_css', $skin_css);
\Macaw::setKey('skin_phone', $skin_phone);
\Macaw::setKey('product_info', $product_info);
\Macaw::setKey('coupon_helper', $couponHelper);
\Macaw::setKey('columnLeftTest2', $columnLeftTest2);
\Macaw::setKey('is_danonwinkel', $danonewinkel);

if (isset($page_cache)) \Macaw::setKey('page_cache', $page_cache);

require(DIR_WS_LANGUAGES . $language . DIRECTORY_SEPARATOR . 'header_tags.php');


$applicationChecker = new ApplicationChecker;
$applicationChecker->check($messageStack);

if ($messageStack->size('header') > 0) {
    echo $messageStack->output('header');
}
$language =  \Macaw::getKey('language') ?  \Macaw::getKey('language') : \Macaw::getConfig('default_language');

Macaw::get('/advanced_search_result_v4.php', function() use ($language) {
    include DIR_WS_LANGUAGES . $language . DIRECTORY_SEPARATOR . FILENAME_ADVANCED_SEARCH;
    $searchController = new SearchController();
    $searchController->indexAction();
});
Macaw::post('/advanced_search_result_v4.php', function() use ($language) {
    include DIR_WS_LANGUAGES . $language . DIRECTORY_SEPARATOR . FILENAME_ADVANCED_SEARCH;
    $searchController = new SearchController();
    $searchController->indexAction();
});
Macaw::get('/specials2.php', function() {
    $searchController = new SearchController('specials');
    $searchController->specialsAction();
});

Macaw::post('/specials2.php', function() {
    $searchController = new SearchController('specials');
    $searchController->specialsAction();
});

Macaw::get('(.*)-mm-(.*).html', function() use ($language) {
    require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_DEFAULT);
    $redirectManager = new RedirectManager(
        new ManufacturerRedirect(
            new \Model\Mysql\Manufacturers(\MysqlDb::getInstance(\Macaw::getConfig('mysql')))
        )
    );
    $redirectManager->validateWithRedirect(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $searchController = new SearchController('brand');
    $searchController->brandAction();
});
Macaw::post('(.*)-mm-(.*).html', function() use ($language) {
    require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_DEFAULT);
    $redirectManager = new RedirectManager(
        new ManufacturerRedirect(
            new \Model\Mysql\Manufacturers(\MysqlDb::getInstance(\Macaw::getConfig('mysql')))
        )
    );
    $redirectManager->validateWithRedirect(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $searchController = new SearchController('brand');
    $searchController->brandAction();
});
Macaw::get('/suggestions2.php', function() use ($language) {
    $searchController = new SearchController();
    $searchController->suggestionsAction();
});

Macaw::console('parse-reviews-xml', function() {
    $searchController = new ConsoleController();
    $searchController->parseReviewsXmlAction();
});

Macaw::console('update-assembla-tickets', function() {
    $searchController = new ConsoleController();
    $searchController->setColors(new \Plugins\Controller\Colors\Colors())
        ->updateAssemblaTickets();
});

Macaw::console('get-info-assembla-tickets', function() {
    $searchController = new ConsoleController();
    $searchController->setColors(new \Plugins\Controller\Colors\Colors())
        ->getInfoAssemblaTickets();
});

Macaw::dispatch();
$enable_page_cache = \Macaw::getKey('enable_page_cache');
/* application_bottom.php */
if ($enable_page_cache) {
    if ($page_cache = \Macaw::getKey('page_cache')) {
        $page_cache->end_page_cache();
    }
}

// close session (store variables)
tep_session_close();

if (STORE_PAGE_PARSE_TIME == 'true') {
    $time_start = explode(' ', PAGE_PARSE_START_TIME);
    $time_end = explode(' ', microtime());
    $parse_time = number_format(($time_end[1] + $time_end[0] - ($time_start[1] + $time_start[0])), 3);
    error_log(strftime(STORE_PARSE_DATE_TIME_FORMAT) . ' - ' . getenv('REQUEST_URI') . ' (' . $parse_time . 's)' . "\n", 3, STORE_PAGE_PARSE_TIME_LOG);

    if (DISPLAY_PAGE_PARSE_TIME == 'true') {
        echo '<span class="smallText">Parse Time: ' . $parse_time . 's</span>';
    }
}

if ( (GZIP_COMPRESSION == 'true') && ($ext_zlib_loaded == true) && ($ini_zlib_output_compression < 1) ) {
    if ( (PHP_VERSION < '4.0.4') && (PHP_VERSION >= '4') ) {
        tep_gzip_output(GZIP_LEVEL);
    }
}
