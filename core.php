<?php
    require_once(__DIR__."/vendor/autoload.php");
    use VK\Client\VKApiClient;

    class Core {
        private $access_token = '05c7d862ff0ffe1dcced37086a48678517571a497519bbf1dc5cbe7dc6a6632eff49cd2ddc5c06c01c291';
        private $vk;
        
        function __construct() {
            $this->vk = new VKApiClient();
        }
        public function groupJoin($group_id, $object) {
            $id = $object['user_id'];
            $user_info = $this->getUserInfo($id);
            
            return $this->setNewImage($group_id, $user_info[0]['photo_200'], $user_info[0]['first_name'] . " " . $user_info[0]['last_name']);
        }
        public function messageNew($group_id, $object) {
            $url = $object['message']->text;
            $user_id = $object['message']->from_id;
            
            $id = explode("/", $url);
            $id = $id[count($id)-1];
            
            try {
                $params = array(
                    "user_ids" => $id,
                    "fields" => "photo_200"
                );
                $dat = $this->vk->users()->get($this->access_token, $params);
                //print_r($dat[0]); return;
                $this->setNewImage($group_id, $dat[0]['photo_200'], $dat[0]['first_name'] . " " . $dat[0]['last_name']);
                
                $params = array(
                    "random_id" => rand(),
                    "user_id" => $user_id,
                    "message" => "ok"
                );
                $this->vk->messages()->send($this->access_token, $params);
            }
            catch (Exception $e) {
                try {
                    $params = array(
                        "random_id" => rand(),
                        "user_id" => $user_id,
                        "message" => "User not found"
                    );
                    $this->vk->messages()->send($this->access_token, $params);
                }
                catch (Exception $e) {}
            }
        }
        private function setNewImage($group_id, $url, $name) {
            $src_folder = __DIR__."/src";
            $tmp_folder = __DIR__."/tmp";
            
            $img = new Img($src_folder, $tmp_folder);
            
            $file = $img->draw($src_folder."/bg.png", $url, $name);
            
            $params = array(
                "group_id" => $group_id,
                "crop_x" => 0,
                "crop_y" => 0,
                "crop_x2" => 1590,
                "crop_y2" => 400
            );
            
            $address = $this->vk->photos()->getOwnerCoverPhotoUploadServer($this->access_token, $params);
            
            $photo = $this->vk->getRequest()->upload($address['upload_url'], 'photo', $file);
            
            return $this->vk->photos()->saveOwnerCoverPhoto($this->access_token, $photo);
        }
        private function getUserInfo($id) {
            $response = $this->vk->users()->get($this->access_token, array(
                'user_ids' => $id,
                'fields' => array('photo_200'),
            ));
            return $response;
        }
    }
?>