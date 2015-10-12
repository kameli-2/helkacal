<?php
class OpenSubmitConfiguration {
        private $data = array();
        function __construct($fpath) {
                $this->readconfig($fpath);
        }
        function is_set($key) { return isset($this->data[$key]); }
        function __get($key) {
                if (isset($this->data[$key])) {
                        return $this->data[$key];
                } else {
                        return array();
                }
        }
        function readconfig($fpath) {
                $lines = file($fpath);
                foreach ($lines as $line) {
                        if (strpos($line,'#')) { $line = substr($line,0,strpos($line,'#')); }
                        if (strpos($line,':')) {
                                list($key,$vals) = explode(':',$line,2);
                                $key = trim($key);
                                $valar = explode(',',$vals);
                                $cleanvalar = array();
                                foreach ($valar as $v) { $cleanvalar[] = trim($v); }
                                $this->data[$key] = $cleanvalar;
                        }
                }
        }
}
?>
