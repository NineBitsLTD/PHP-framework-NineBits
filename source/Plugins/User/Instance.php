<?php

namespace User;

class Instance extends \Sys\Object {    
    /**
     * Идентификатор пользователя
     * 
     * @var int
     */
    public $Id=0;
    /**
     *
     * @var type 
     */
    public $Status=0;
    /**
     * Пароль пользователя
     * 
     * @var string
     */
    protected $Password="";
    
    /**
     * Имя или емаил или телефон пользователя
     * 
     * @var string
     */
    public $Name="";
    /**
     * Группа пользователей
     *
     * @var \Sys\User\Group 
     */
    public $Group = null;
    /**
     * Группа прав доступа
     * 
     * @var \User\Role 
     */
    public $Role = null;
    /**
     * Адресс пользователя
     *
     * @var string
     */
    public $AddressIP = "";
    /**
     * Пользователь авторизирован
     *
     * @var boolean
     */
    public $IsLogged = false;
    
    /**
     * Инициализация пользователя
     * 
     * @param \Sys\Registry $registry Конфигурация проекта
     */
    public function __construct(&$registry) {
        parent::__construct($registry);
        $this->Login();
    }
    public function __destruct() {}

    /**
     * Инициализация пользователя
     * 
     * @param \Sys\DataBase\ActiveRecord $userRecord Запись о пользователе из базы данных
     */
    public function Login($name=null, $password=null){  
        $userModel = new \User\Model\User($this->Reg);
        if(!isset($name) || !isset($password)){
            if( array_key_exists('user', $this->Reg->Session->Data) && 
                array_key_exists('id', $this->Reg->Session->Data['user']))
                $userRecord = $userModel->GetById($this->Reg->Session->Data['user']['id']);
            else $userRecord = new \Sys\DataBase\ActiveRecord($this->Reg, $this->Reg->DB->Provider);
        } else {            
            $this->Name = $name;
            $this->Password = \Sys\Helper::$Security->EscapeDecodeHtml($password);
            $userRecord = $userModel->GetByPassword($this->Name, $this->Password);
        }
        $groupModel = new \User\Model\Group($this->Reg);
        if($userRecord->Count==1 && array_key_exists('id', $userRecord->Row) && array_key_exists('username', $userRecord->Row)){
            $this->Reg->Session->Data['user'] = [
                'id'=>$userRecord->Row['id'],
                'ip'=>$this->Reg->Request->Server['REMOTE_ADDR']
            ];
            $this->Id = $userRecord->Row['id'];
            $this->Name = $userRecord->Row['username'];
            $this->Status = $userRecord->Row['status'];
            if(array_key_exists('group_id', $userRecord->Row)) $this->Group = $userRecord->Row['group_id'];
            if(array_key_exists('role_id', $userRecord->Row)){
                $this->Role = new \User\Role($this->Reg, $userRecord->Row['role_id']);
            }
            $this->AddressIP = $this->Reg->Request->Server['REMOTE_ADDR'];
            $userModel->TimeUpdate($this->Id);
            if($this->Status>4) $this->Reg->Request->PathDefault = "home";
            else $this->Reg->Request->PathDefault = "guest";
            $this->IsLogged = true;
        } else {
            $this->IsLogged = false;
        }
    }
    public function Logout(){
        unset($this->Reg->Session->Data['user']);
        $this->Id = 0;
        $this->Password = "";
        $this->Name = "";
        $this->Group = null;
        $this->Role = null;
        $this->AddressIP = "";
        $this->IsLogged = false;
        $this->Reg->Session->Destroy();
    }
    /**
     * Печать информации пользователя
     * 
     * @param string $id Идентификатор пользователя
     * @param boolean $edit Разрешена ли правка пользователя
     * @param boolean $info Отображать ли дополнительную информацию
     * @return string
     */
    public function PrintInfo($id, $edit = false, $info = false){
        $modelUser = new \User\Model\User($this->Reg);
        $records = $modelUser->GetById($id);
        $item = $records->Row;
        if(array_key_exists('id', $item)) $item['childrens'] = $modelUser->GetTotal("`user_id`=".(int)$item['id']);
        else $item['childrens'] = [];
        return $this->Reg->View->Render('profile_item', [
            'item' => $item,
            'edit' => $edit,
            'info' => $info,
            'base' => $this->Reg->Link(),
        ]);
    }
}

