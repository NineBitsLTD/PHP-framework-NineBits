<?php

namespace Controller\Project\Create;

class CoreComponents extends \Controller\Home { 
    public function methodIndex($data = array()) {
        $data['CoreList'] = \Helper::$File->PHP->GetListPhpFilename(dirname(dirname(dirname(__DIR__))).'/Core');
        parent::methodIndex($data);
    }
}

