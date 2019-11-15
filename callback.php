<?php
    require_once(__DIR__."/vendor/autoload.php");
    use VK\CallbackApi\Server\VKCallbackApiServerHandler;
    use VK\Client;

    class ServerHandler extends VKCallbackApiServerHandler {
        const SECRET = 'xh1928x10x1c2';
        const GROUP_ID = 123999;
        const CONFIRMATION_TOKEN = 'e67anm1';

        function confirmation(int $group_id, ?string $secret) {
            if ($secret === static::SECRET && $group_id === static::GROUP_ID) {
                echo static::CONFIRMATION_TOKEN;
            }
        }

        public function messageNew(int $group_id, ?string $secret, array $object) {
            if ($secret!=self::SECRET) return $this->exception(0);
            
            $core = new Core();
            $core->messageNew($group_id, $object);
            
            echo 'ok';
        }
        public function groupJoin(int $group_id, ?string $secret, array $object) {
            if ($secret!=self::SECRET) return $this->exception(0);
            
            $core = new Core();
            $core->groupJoin($group_id, $object);
            
            echo 'ok';
        }
        private function exception($code) {
            $codes=["Bad secret code"];
            
            if (isset($codes[$code])) {
                throw new Exception($codes[$code]);
            }
            else {
                throw new Exception("Unknown error");
            }
        }
    }

    $handler = new ServerHandler();
    $data = json_decode(file_get_contents('php://input'));
    $handler->parse($data);
?>