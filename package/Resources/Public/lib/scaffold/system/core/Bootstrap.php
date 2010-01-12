<?php defined('SYSPATH') OR die('No direct access allowed.');

define('SCAFFOLD_VERSION',  '1.5b3');

require SYSPATH . '/core/Common.php';
require SYSPATH . '/core/Benchmark.php';
require SYSPATH . '/core/Plugins.php';
require SYSPATH . '/core/CSScaffold.php';
require SYSPATH . '/core/CSS.php';

require SYSPATH . '/vendor/FirePHPCore/fb.php';
require SYSPATH . '/vendor/FirePHPCore/FirePHP.class.php';

# Send the request through to the main controller
CSScaffold::setup($_GET);

# Parse the css
CSScaffold::parse_css();

# Send it to the browser
CSScaffold::output_css();