<?php

// this file setup the proper include path and the very basic critical files
// no execution logic

// need to SET/override the following required files
require 'config/dev.php';
require 'config/path.php';

$project_path = realpath(dirname(__FILE__).'/../');
ini_set('include_path', ini_get('include_path') . ':' . $project_path);

require 'common/std.php';