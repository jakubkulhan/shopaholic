<?php
// loader
require LIB_DIR . '/Nette/loader.php';
$loader = new RobotLoader();
$loader->addDirectory(APP_DIR);
$loader->addDirectory(LIB_DIR);
$loader->autoRebuild = TRUE;
$loader->register();

// configure
foreach (require Environment::expand('%settingsFile%') as $k => $v) {
    Environment::setVariable($k, $v);
}

foreach (require Environment::expand('%adminLoginFile%') as $k => $v) {
    define('ADMIN_' . strtoupper($k), $v);
}

foreach (require Environment::expand('%commonFile%') as $k => $v) {
    Environment::setVariable($k, $v);
}

if (!function_exists('date_default_timezone_set')) {
    function date_default_timezone_set($timezone)
    {
        ini_set('date.timezone', $timezone);
    }
}

date_default_timezone_set(require Environment::expand('%timezoneFile%'));

// debugging
Debug::enable(NULL, BASE_DIR . '/error.log', Environment::expand('%adminEmail%'));

// paths
Environment::setVariable('themeDir', Environment::expand('%baseDir%/themes'));
Environment::setVariable('templatesDir', Environment::expand('%themeDir%/%theme%'));
Environment::setVariable('tempDir', Environment::expand('%baseDir%/tmp'));
Environment::setVariable('themeBaseUri', Environment::expand('%baseUri%/themes/%theme%'));
Environment::setVariable('mediaDir', Environment::expand('%baseDir%/media'));
Environment::setVariable('mediaBaseUri', Environment::expand('%baseUri%/media'));

set_include_path(LIB_DIR . PATH_SEPARATOR . get_include_path());

Html::$xhtml = FALSE;
SafeStream::register();
setlocale(LC_ALL, require Environment::expand('%localeFile%'));
Zend_Search_Lucene::setDefaultSearchField('description');

// configure locale
require_once LIB_DIR . '/tr.php';
$available = array();
foreach (glob(APP_DIR . '/locale/' . '*.php') as $_) {
    $available[substr(substr($_, strlen(APP_DIR . '/locale/')), 0, 2)] = $_;
}
tr::$locale = Environment::getHttpRequest()->detectLanguage(array_keys($available));
if (tr::$locale) {
    list(tr::$plurals[tr::$locale], tr::$table[tr::$locale]) = require $available[tr::$locale];
}

// connect to DB
dibi::connect(require Environment::expand('%dbFile%'));

// get app
$app = Environment::getApplication();

// routing
$router = $app->getRouter();

Route::setStyleProperty('action', Route::FILTER_TABLE, array(
    __('show-cart') => 'showCart',
    __('fill-data') => 'fillData',
    __('complete')  => 'complete',
    __('commit')    => 'commit'
));
$router[] = new Route(__('order') . '/<action>/', 
    array('presenter' => 'Front:Order'));

$router[] = new Route('admin/<presenter>/<action>/', 
    array('module' => 'Back', 'presenter' => 'Dashboard', 'action' => 'default'));

$router[] = new Route(__('search') . ' ? q=<q .*> & ' . __('page') . '=<page \d+>', 
    array('presenter' => 'Front:Search', 'action' => 'default', 'page' => 1));

Route::addStyle('path', NULL);
Route::setStyleProperty('path', Route::PATTERN, '.*');
$router[] = new Route('<path>/ ? ' . __('page') . '=<page_number \d+> & ' . __('letter') . '=<letter [A-Z]|#>', 
    array(
        'presenter' => 'Front:Show', 
        'action' => 'default', 
        'path' => '', 
        'page_number' => 1,
        'letter' => NULL
    ));


// run!
$app->run();
