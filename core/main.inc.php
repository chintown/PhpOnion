<?php
    // this file setup the proper include path and the very basic critical files
    // no execution logic

    /* for self-contain testing only */
    /**
    $project_path = realpath(dirname(__FILE__).'/../');
    ini_set('include_path', ini_get('include_path') . ':' . $project_path);
    //*/

    /**/ //__PARENT_PROJECT__
    require 'config/dev.php';
    if (DEV_MODE) {ini_set('apc.enabled',0);}
    require 'config/prerequisite.php';

    $project_path = realpath(dirname(__FILE__).'/../');
    ini_set('include_path', ini_get('include_path') . ':' . $project_path);

    require 'config/path.php';
    require 'common/std.php';
    //*/

    /** //__CHILD_PROJECT__
    $project_path = realpath(dirname(__FILE__).'/../');
    ini_set('include_path', ini_get('include_path') . ':' . $project_path);
    // var_dump(ini_get('include_path'));
    require '/Users/chintown/src/php/PhpOnion/' . 'core/main.inc.php'; // __PARENT_ROOT__
    //*/