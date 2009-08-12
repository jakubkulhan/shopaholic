<?php
define('BASE_DIR', dirname(__FILE__));
define('APP_DIR', BASE_DIR . '/shopaholic');
define('LIB_DIR', APP_DIR . '/lib');
define('CONF_DIR', BASE_DIR . '/conf');
define('SETTINGS_FILE', CONF_DIR . '/settings.php');
define('ADMIN_LOGIN_FILE', CONF_DIR . '/adminlogin.php');
define('TITLE_PAGE_FILE', CONF_DIR . '/titlepage.php');
define('DB_FILE', CONF_DIR . '/db.php');
define('COMMON_FILE', CONF_DIR . '/common.php');
define('TIMEZONE_FILE', CONF_DIR . '/timezone.php');
define('LOCALE_FILE', CONF_DIR . '/locale.php');
define('SESSION_ORDER_NS', 'order');
define('FULLTEXT_DIR', BASE_DIR . '/fulltext');
define('ADMINLOG_DIR', BASE_DIR . '/log/admin');

require_once APP_DIR . '/bootstrap.php';
