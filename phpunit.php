<?php

use Fisharebest\Webtrees\I18N;

chdir(dirname(__FILE__));

require 'vendor/autoload.php';

define('WT_WEBTREES', 'webtrees');
define('WT_BASE_URL', '');
define('WT_DATA_DIR', 'tmp/data/');
define('WT_DEBUG_SQL', false);
define('WT_REQUIRED_MYSQL_VERSION', '5.0.13');
define('WT_REQUIRED_PHP_VERSION', '5.3.2');
define('WT_MODULES_DIR', 'vendor/fisharebest/webtrees/modules_v3/');
define('WT_ROOT', 'vendor/fisharebest/webtrees/');

// Regular expressions for validating user input, etc.
define('WT_MINIMUM_PASSWORD_LENGTH', 6);
define('WT_REGEX_XREF', '[A-Za-z0-9:_-]+');
define('WT_REGEX_TAG', '[_A-Z][_A-Z0-9]*');
define('WT_REGEX_INTEGER', '-?\d+');
define('WT_REGEX_BYTES', '[0-9]+[bBkKmMgG]?');
define('WT_REGEX_IPV4', '\d{1,3}(\.\d{1,3}){3}');
define('WT_REGEX_PASSWORD', '.{' . WT_MINIMUM_PASSWORD_LENGTH . ',}');

I18N::init('en-US');