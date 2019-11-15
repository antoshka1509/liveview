<?php
    class Img {
        private $bg_img;
        private $cache_file;
        private $src_folder;
        private $tmp_folder;
        
        public function __construct($src_folder = null, $tmp_folder = null) {
            $this->tmp_folder = __DIR__."/tmp";
            if ($tmp_folder!=null)
                $this->tmp_folder = $tmp_folder;
            
            $this->src_folder = __DIR__."/src";
            if ($src_folder!=null)
                $this->src_folder = $src_folder;
            
            $this->cache_file = $this->tmp_folder."/cache.json";
        }   
        public function draw($bg_img, $user_img_url, $name) {
            $img = new Imagick();
            $img->newImage(1590, 400, new \ImagickPixel('white'));
            
            // PRINTING USER PICTURE
            {
                $user_img = $this->download($user_img_url);
                $pos = $this->getPos($bg_img);
                $userpic = new Imagick();
                $userpic->readImage($user_img);
                $img->compositeImage($userpic, Imagick::COMPOSITE_DEFAULT, $pos[0], $pos[1]);
                $userpic->clear();
            }
            
            // PRINTING BACKGROUND IMAGE
            { 
                $bg = new Imagick();
                $bg->readImage($bg_img);
                $img->compositeImage($bg, Imagick::COMPOSITE_DEFAULT, 0, 0);
                $bg->clear();
            }
            
            // PRINTING TEXT
            { 
                $font_size = 30;
                $x = $pos[0] + 250;
                $y = $pos[1] + (200+$font_size)/2;
                
                $textsettings = new ImagickDraw();
                $textsettings->setFillColor('black');
                $textsettings->setFont($this->src_folder."/font.ttf");
                $textsettings->setFontSize($font_size);

                $img->annotateImage($textsettings, $x, $y, 0, $name);
                $textsettings->clear();
            }
            
            $output = $this->tmp_folder."/t.jpg";
            $fileHandle = fopen($output, "w");
            $img->writeImageFile($fileHandle, "JPG");
            fclose($fileHandle);
            $img->clear();
            return $output;
        }
        private function getPos($image) {
            $cache = $this->getCache();
            if ($cache->time >= filemtime($image) && $cache->file == $image) {
                return [$cache->pos[0], $cache->pos[1]];
            }
            
            $img = new Imagick($image);
            $width = $img->getImageWidth();
            $height = $img->getImageHeight();
            $pos = [$width, $height];
            
            for ($i = 0; $i < $width; $i++) {
                for ($j = 0; $j < $height; $j++) {
                    $pixel = $img->getImagePixelColor($i, $j);
                    $color = $pixel->getColor();
                    if ($color['a']<1)
                        $pos = [min($i, $pos[0]), min($j, $pos[1])];
                    $pixel->destroy();
                }
            }
            
            $img->clear();
            $this->saveCache($image, $pos);
            return $pos;
        }
        private function saveCache($file, $pos) {
            $arr = array(
                "time" => time(),
                "file" => $file,
                "pos" => [$pos[0], $pos[1]]
            );
            return file_put_contents($this->cache_file, json_encode($arr));
        }
        private function getCache() {
            return json_decode(file_get_contents($this->cache_file));
        }
        private function download($url) {
            $output = $this->tmp_folder."/userpic.jpg";
            file_put_contents($output, file_get_contents($url));
            return $output;
        }
    }
?>