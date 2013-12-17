<?php

class Response {
    private $content_type;
    private $status;
    private $result;
    private $request;
    private $logs;
    private $blockExternal;

    public function __construct() {
        $this->content_type = 'application/json';
        $this->status       = 200;
        $this->headers      = array();
        $this->result       = array();
        $this->format       = 'json'; // output format
        $this->request;
        $this->logs         = array();
        $this->chain_logs   = array();
        $this->blockExternal= true;
    }
    public function setContentType($type) {
        $this->content_type = $type;
    }
    public function setStatus($status) {
        $this->status = $status;
    }
    public function addHeaders($header) {
        if (is_array($header)) {
            $this->headers = array_merge($this->headers, $header);
        } else {
            $this->headers[] = $header;
        }
    }
    public function setResult($result) {
        $this->result = $result;
    }
    public function setFormat($format) {
        $this->format = $format;
    }
    public function setRequest($request) {
        $this->request = $request;
    }
    public function addLog($msg) {
        if (is_array($msg)) {
            $this->logs = array_merge($this->logs, $msg);
        } else {
            $this->logs[] = $msg;
        }
    }
    public function addChainLog($msg) {
        if (is_array($msg)) {
            $this->chain_logs = array_merge($this->chain_logs, $msg);
        } else {
            $this->chain_logs[] = $msg;
        }
    }
    public function setBlockExternal($block) {
        $this->blockExternal = $block;
    }

    public function getResult() {
        return $this->result;
    }
    public function getFormat() {
        return $this->format;
    }

    private function whetherExternal () {
        //$ref = $_SERVER['HTTP_REFERER'];
        //$refInfo = parse_url($ref);
        //$isExternal = ($refInfo['host'] !== SERVER_HOST);
        #bde('SERVER.HTTP_HREFERER:' . $refInfo['host']);
        #bde('DEFINE.SERVER_HOST:' . SERVER_HOST);

        session_start();
        $isExternal = (isset($_SESSION['ID']) && !empty($_SESSION['id']));

        return $isExternal;
    }
    public function render($format='json') {
        $isExternal = $this->whetherExternal();

        if (DEV_MODE) {
            // pass
        } else if (!$this->blockExternal) {
            // pass
        } else if ($isExternal) {
            header("HTTP/1.1 400 " . RestUtils::getStatusCodeMessage(400));
            header("Content-Type: text/plain; charset=UTF-8");
            echo 'block';
            return;
        }

        // status_header
        header("HTTP/1.1 ". $this->status ." " . RestUtils::getStatusCodeMessage($this->status));
        // customized headers
        foreach ($this->headers as $key=> $value) {
            header("$key: $value");
        }

        // data payload
        if ($format === 'json') {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($this->result);
        } else if ($format === 'image') {
            header("Content-Type: ".$this->content_type);
            $result = $this->result;
            if ($result['path'] !== null) {
                echo file_get_contents($result['path']);
            }
        } else if ($format === 'text') {
            header("Content-Type: text/plain; charset=UTF-8");
            $result = $this->result;
            echo $result;
        } else if ($format === 'debug') {
            header("Content-Type: text/plain; charset=UTF-8");
            var_dump($this->result);
        } else {
            header("Content-Type: " . $format);
            if ($this->result['path'] !== null) {
                echo file_get_contents($this->result['path']);
            }
        }
    }
    public function dumpRequest() {
        $this->request->http_accept = '...purged...';
        return var_export($this->request, true);
    }
    public function dumpResponse() {
        return var_export($this->result, true);
    }
    public function dumpLogs() {
        if (empty($this->logs)) {
            return <<<HINT
   ____  _  __
  / __ \| |/ /
 | |  | | ' /
 | |  | |  <
 | |__| | . \
  \____/|_|\_\
HINT;

        } else {
            var_export($this->logs, true);
        }
    }
    public function dumpChainLogs() {
        return var_export($this->chain_logs, true);
    }
}
