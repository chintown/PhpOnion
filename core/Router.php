<?php

require_once 'lib/spyc/spyc.php';

class Router {
    var $routes; // pattern => entry
    var $testCases;

    const REGEX_ANY = "[^/]+";
    const REGEX_INT = "[0-9]+";
    const REGEX_ALPHA = "[a-zA-Z_-]+";
    const REGEX_ALPHANUMERIC = "[0-9a-zA-Z_-]+";
    const REGEX_STATIC = "%s";

    public function __construct($file_or_array) {
        $this->routes = array();
        $this->testCases = array();

        $route_manifest = (is_string($file_or_array))
                            ? spyc_load_file($file_or_array)
                            : $file_or_array;

        foreach ($route_manifest as $entry => $profile) {
            if (empty($profile['patterns'])) {
                throw new Exception("missing configuration of 'pattern' in '$entry'' entry");
            } else if (empty($profile['samples'])) {
                throw new Exception("missing configuration of 'samples' in '$entry'' entry");
            }
            foreach ($profile['patterns'] as $pattern) {
                $pattern = $this->convertPatternToRegexp($pattern);
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
//            echo "\n";

            preg_match($pattern, $raw, $matches);
            if (empty($matches)) {
                continue;
            }
            $matched_entry = $entry;
            $matched_params = $this->filterMatchesWithNumericKey($matches);

//            var_dump($matches);
//            var_dump($matched_params);
//            echo "\n";
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

    private function filterMatchesWithNumericKey($matches) {
        $result = array();
        foreach($matches as $key => $val) {
            if (!is_numeric($key)) {
                $result[$key] = $val;
            }
        }
        return $result;
    }

    private function convertPatternToRegexp($pattern) {
        $parts = explode('/', $pattern);
        $regexParts = array();
        foreach ($parts as $part) {
            $regexParts[] = $this->convertPartToRegexp($part);
        }
        return implode('/', $regexParts);
    }
    public function convertPartToRegexp($part) {
        $args = explode(':', $part);
        if (count($args) < 2) { // has no shorthand in pattern. e.g. int:key_name
            return $part;
        }

        if (substr($part, -1) === '?') {
            $allow_missing = '?';
            $part = substr($part, 0, -1);
        } else {
            $allow_missing = '';
        }
        $args = explode(':', $part);

        $type = $args[0];
        $name = $args[1];
        $regexPart = '';
        switch (strtolower($type)) {
            case "int":
            case "integer":
                $regexPart = self::REGEX_INT;
                break;
            case "alpha":
                $regexPart = self::REGEX_ALPHA;
                break;
            case "alphanumeric":
            case "alphanum":
            case "alnum":
                $regexPart = self::REGEX_ALPHANUMERIC;
                break;
            default:
                $regexPart = self::REGEX_ANY;
                break;
        }
        return "(?P<$name>$regexPart)$allow_missing";
    }
}