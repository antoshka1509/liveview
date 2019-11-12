<?php
    require_once(__DIR__."/photos.php");
    require_once(__DIR__."/users.php");
    require_once(__DIR__."/messages.php");
    
    class VK {
        private $token;
        const VK_BASE_RUL = "https://api.vk.com/method/";
        public $users;
        public $photos;
        public $messages;
        
        public function __construct($token) {
            $this->token = $token;
            $this->photos = new Photos($this);
            $this->messages = new Messages($this);
            $this->users = new Users($this);
        }
        public function requestGet($method, $params = null) {
            $params["access_token"] = $this->token;
            $params["v"] = "5.103";
            $data = json_decode(file_get_contents(self::VK_BASE_RUL.$method."?".http_build_query($params)));
            if (isset($data->error)) {
                return $this->exception($data->error->error_code, $data->error->error_msg);
            }
            return $data;
        }
        public function requestPost($url, $params = null, $files = null) {
            if (count($files)>0) {
                define('BOUNDARY', '--------------------------'.microtime(true));
                $header = 'Content-Type: multipart/form-data; boundary='.BOUNDARY;
                foreach ($files as $field=>$file) {
                    $content .=  "--".BOUNDARY.PHP_EOL.
                        "Content-Disposition: form-data; name=\"".$field."\"; filename=\"".basename($file)."\"".PHP_EOL.
                        "Content-Type: ".mime_content_type($file).PHP_EOL.PHP_EOL.
                        file_get_contents($file).PHP_EOL;
                }
                
                foreach ($params as $param=>$value) {
                    $content .= "--".BOUNDARY.PHP_EOL.
                        "Content-Disposition: form-data; name=\"".$param."\"".PHP_EOL.PHP_EOL.
                        $value.PHP_EOL;
                }
                
                $content .= "--".BOUNDARY."--".PHP_EOL;
                $context = stream_context_create(array(
                    'http' => array(
                          'method' => 'POST',
                          'header' => $header,
                          'content' => $content,
                    )
                ));
            }
            else {
                $postdata = http_build_query($params);
                $context = stream_context_create(array('http' =>
                    array(
                        'method'  => 'POST',
                        'header'  => 'Content-Type: application/x-www-form-urlencoded',
                        'content' => $postdata
                    )
                ));
            }
            
            $data = json_decode(file_get_contents($url, false, $context));
            if (isset($data->error)) {
                return $this->exception($data->error->error_code, $data->error->error_msg);
            }
            return $data;
        }
        private function exception($code, $msg="") {
            if ($code=="") {
                throw new Exception("Unknown error");
            }
            else {
                throw new Exception(trim($code.PHP_EOL.$msg));
            }
        }
    }
?>