<?php
    class Messages {
        private $base;
        
        public function __construct($base) {
            $this->base = $base;
        }
        public function send($params) {
            $method = "messages.send";
            return $this->base->requestGet($method, $params);
        }
    }
?>