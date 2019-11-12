<?php
    class Users {
        private $base;
        
        public function __construct($base) {
            $this->base = $base;
        }
        public function get($params) {
            $method = "users.get";
            return $this->base->requestGet($method, $params);
        }
    }
?>