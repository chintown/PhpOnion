<?php

require_once 'lib/spyc/spyc.php';

class Router {
    var $routes; // pattern => entry
    var $testCases;

    public function __construct($file_or_array) {
        $this->routes = array();
        $this->testCases = array();

        $route_manifest = (is_string($file_or_array))
                            ? spyc_load_file($file_or_array)
                            : $file_or_array;

        foreach ($route_manifest as $entry => $profile) {
            foreach ($profile['patterns'] as $pattern) {

                $pattern = '&'.$pattern.'&';
                if (!empty($this->routes[$pattern])) {
                    die("duplicated routes in manifest: ". $pattern);
                }

                $this->routes[$pattern] = $entry;
            }

            if (isset($profile['samples'])) {
                foreach ($profile['samples'] as $test) {
                    $this->testCases[$test] = $entry;
                }
            }
        }
    }

    public function parse($raw, &$rest_path_params) {
        $matched_entry = null;
        $matched_params = array();
        foreach ($this->routes as $pattern => $entry) {
//            var_dump($pattern);
//            var_dump($raw);

            preg_match($pattern, $raw, $matches);
            if (empty($matches)) {
                continue;
            }
            $matched_entry = $entry;
            $matched_params = $this->filter_matches_with_numeric_key($matches);

//            var_dump($matches);
//            var_dump($matched_params);
        }
        $rest_path_params = $matched_params;
        return $matched_entry;
    }

    public function testAll() {
        foreach ($this->testCases as $case => $answer) {
            $result = $this->parse($case, $params);
            if ($result != $answer) {
                die("mismatched route: \n".$case."\n".$answer."\n");
            }
        }

        echo "OK\n";
    }

    private function filter_matches_with_numeric_key($matches) {
        $result = array();
        foreach($matches as $key => $val) {
            if (!is_numeric($key)) {
                $result[$key] = $val;
            }
        }
        return $result;
    }
}