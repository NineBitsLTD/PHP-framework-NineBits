<?php

namespace Controller;

class NotFound extends \Controller\Home {
    
    public function methodIndex($data = array()) {
        if(array_key_exists('msg', \Registry::$Request->Get)) $data['msg'] = \Registry::$Request->Get['msg'];
        parent::methodIndex($data);
    }
}