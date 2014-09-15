<?php
// copy from PhpOnion/tool/_validate_routes.php
$pwd = dirname(__FILE__).'/';
require $pwd.'../core/main.inc.php';
require "core/Router.php";
$r = new Router($argv[1]);
$r->testAll();