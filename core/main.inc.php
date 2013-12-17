<?php

//$project_path = realpath(dirname(__FILE__).'/../');
//ini_set('include_path', ini_get('include_path') . ':' . $project_path);
//// var_dump(ini_get('include_path'));

require 'config/dev.php';
require 'config/path.php';

$project_path = realpath(dirname(__FILE__).'/../');
ini_set('include_path', ini_get('include_path') . ':' . $project_path);

require 'common/std.php';
require 'core/controller.php';
