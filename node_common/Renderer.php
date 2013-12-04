<?php
  class Renderer extends BaseNode {
    function __construct() {
    }
    public function execute($req, $res) {
        $this->next($req, $res);

        $debug = DEV_MODE && $req->getDebug();

        if ($debug) {
            echo "<code>";
            echo "\n[OUTPUT]\n";
            echo "</code>";
        }
        $format = $res->getFormat();
        $format = ($debug) ? 'debug' : $format;
        $res->render($format);

        if ($debug) {
            echo "<code>";
            echo "\n[REQUEST]\n";
            echo $res->dumpRequest();

            echo "\n[LOGS]\n";
            echo $res->dumpLogs();
            echo "</code>";
        }
    }
  }