<?php
class RestUtils {
    public static function processRequest() {
    }

    public static function sendResponse($status = 200, $body = '', $content_type = 'text/html') {
    }

    public static function getStatusCodeMessage($status) {
        // these could be stored in a .ini file and loaded
        // via parse_ini_file()... however, this will suffice
        // for an example
        $codes = Array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => '(Unused)',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
            );

        return (isset($codes[$status])) ? $codes[$status] : '';
    }

    public static function requestByGet($url, $headers) {
        $handle = curl_init();
        return self::request($url, $headers, $handle);
    }

    public static function requestByPost($url, $headers, $data) {
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($handle, CURLOPT_POSTFIELDS, $data);
        return self::request($url, $headers, $handle);
    }

    public static function request($url, $headers, $handle) {
        if (!empty($headers)) {
            curl_setopt($handle, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_URL, $url);

        $stdout = fopen('php://stdout', 'w');
        curl_setopt($handle, CURLOPT_VERBOSE, true);
        curl_setopt($handle, CURLOPT_STDERR, $stdout);

        $response = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $header = curl_getinfo($handle, CURLINFO_HEADER_OUT);
        fclose($stdout);
        curl_close($handle);
        return array(
            'code' => $code,
            'response' => $response,
            'header' => $header
        );
    }
}