<?php
    class Img {
        private $bg_img;
        private $cache_file;
        
        public function __construct() {
            $this->bg_img = __DIR__."/../bg.png";
            $this->cache_file = __DIR__."/cache.json";
        }
        
        public function draw($url, $name, $test=false) {
            $png = imagecreatefrompng($this->bg_img);
            list($newwidth, $newheight) = getimagesize($this->bg_img);
            
            $out = imagecreatetruecolor($newwidth, $newheight);
            
            ///////PRINTING USER PIC
            $pos = $this->getPos();
            $img = $this->download($url);
            $jpeg = imagecreatefromjpeg($img);
            list($width, $height) = getimagesize($img);
            imagecopyresampled($out, $jpeg, $pos[0], $pos[1], 0, 0, 200, 200, $width, $height);
            
            
            //PRINTING BG
            imagecopyresampled($out, $png, 0, 0, 0, 0, $newwidth, $newheight, $newwidth, $newheight);
            
            
            //PRINTING NAME
            $color=imagecolorallocate($out, 56, 56, 56);
            $font_size = 30;
            $x = $pos[0] + 250;
            $y = $pos[1] + (200+$font_size)/2;
            imagettftext($out, $font_size, 0, $x, $y, $color, __DIR__."/../font.ttf", $name);
            
            
            $output = __DIR__."/tmp.jpg";
            if ($test) {
                return imagejpeg($out);
            }
            imagejpeg($out, $output);
            return $output;
        }
        private function download($url) {
            $file = __DIR__."/tmp.jpg";
            
            $data = file_get_contents($url);
            file_put_contents($file, $data);
            return $file;
        }
        private function getPos() {
            $cache = $this->getCache();
            if ($cache->time >= filemtime($this->bg_img)) {
                return [$cache->pos[0], $cache->pos[1]];
            }
            
            $img = imagecreatefrompng($this->bg_img);
            $size = getimagesize($this->bg_img);
            
            $pos = [$size[0], $size[1]];
            
            for ($i = 0; $i<$size[0]; $i++) {
                for ($j = 0; $j<$size[1]; $j++) {
                    $alpha = imagecolorsforindex($img, imagecolorat($img, $i, $j))["alpha"];
                    if ($alpha!=0) {
                        $pos = [min($pos[0], $i), min($pos[1], $j)];
                    }
                }
            }
            
            imagedestroy($img);
            $this->saveCache($pos);
            return $pos;
        }
        private function saveCache($pos) {
            $arr = array(
                "time" => time(),
                "pos" => [$pos[0], $pos[1]]
            );
            return file_put_contents($this->cache_file, json_encode($arr));
        }
        private function getCache() {
            return json_decode(file_get_contents($this->cache_file));
        }
    }
?>