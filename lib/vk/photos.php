<?php
    class Photos {
        private $base;
        
        public function __construct($base) {
            $this->base = $base;
        }
        public function saveOwnerCoverPhoto($params) {
            $method = "photos.saveOwnerCoverPhoto";
            return $this->base->requestGet($method, $params);
        }
        public function getOwnerCoverPhotoUploadServer($params) {
            $method = "photos.getOwnerCoverPhotoUploadServer";
            return $this->base->requestGet($method, $params);
        }
    }
?>