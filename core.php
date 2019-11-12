<?php
    require_once(__DIR__."/lib/vk/vk.php");
    require_once(__DIR__."/lib/img.php");
    
    class core {
        private $token = ""; //VK TOKEN
        private $secret = ""; //VK SECRET
        private $confirmation = ""; //VK CONFIRM
        private $answer = "ok";
        private $vk;
        
        public function __construct($data) {
            
            if ($data->secret!=$this->secret) return $this->exception(0);
            
            if ($data->type=="confirmation") {
                $this->answer = $this->confirmation;
            }
            elseif ($data->type=="group_join") {
                $this->vk = new VK($this->token);
                $this->groupJoin($data);
            }
            elseif ($data->type=="message_new") {
                $this->vk = new VK($this->token);
                $this->messageNew($data);
            }
        }
        private function groupJoin($data) {
            $id = $data->object->user_id;
            $group_id = $data->group_id;
            
            $params = array(
                "user_ids" => $id,
                "fields" => "photo_200"
            );
            $dat = $this->vk->users->get($params);
            
            $this->setNewImage($group_id, $dat->response[0]->photo_200, $dat->response[0]->first_name . " " . $dat->response[0]->last_name);
        }
        private function messageNew($data) {
            $url = $data->object->message->text;
            $user_id = $data->object->message->from_id;
            $group_id = $data->group_id;
            
            $id = explode("/", $url);
            $id = $id[count($id)-1];
            
            try {
                $params = array(
                    "user_ids" => $id,
                    "fields" => "photo_200"
                );
                $dat = $this->vk->users->get($params);
                $this->setNewImage($group_id, $dat->response[0]->photo_200, $dat->response[0]->first_name . " " . $dat->response[0]->last_name);
                
                $params = array(
                    "random_id" => rand(),
                    "user_id" => $user_id,
                    "message" => "ok"
                );
                $this->vk->messages->send($params);
            }
            catch (Exception $e) {
                try {
                    $params = array(
                        "random_id" => rand(),
                        "user_id" => $user_id,
                        "message" => "User not found"
                    );
                    $this->vk->messages->send($params);
                }
                catch (Exception $e) {}
            }
        }
        private function setNewImage($group_id, $url, $name) {
            $img = new Img();
            $file = $img->draw($url, $name);
            
            $params = array(
                "group_id" => $group_id,
                "crop_x" => 0,
                "crop_y" => 0,
                "crop_x2" => 1590,
                "crop_y2" => 400
            );
            $upload_url = $this->vk->photos->getOwnerCoverPhotoUploadServer($params)->response->upload_url;
            
            $response = $this->vk->requestPost($upload_url, null, array("photo"=>$file));
            
            $params = array(
                "hash" => $response->hash,
                "photo" => $response->photo
            );
            
            return $this->vk->photos->saveOwnerCoverPhoto($params);
        }
        public function answer() {
            return $this->answer;
        }
        private function exception($code) {
            $codes = ["Bad secret code"];
            
            if ($codes[$code]=="") {
                throw new Exception("Unknown error");
            }
            else {
                throw new Exception($codes[$code]);
            }
        }
    }
?>