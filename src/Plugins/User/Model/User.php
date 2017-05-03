<?php

namespace User\Model;

class User extends \Sys\Model {
    protected $TableName = "users";
    
    public function GetByPassword($name, $password){        
        $records = $this->Reg->DB->Query(
            "SELECT * FROM `".$this->GetTableName()."` WHERE (`email`='".$this->Escape($name).
            "' AND `email` IS NOT NULL AND `email` <> '') OR (`username`='".$this->Escape($name).
            "' AND `username` IS NOT NULL AND `username` <> '') OR (`phone`='".$this->Escape($name).
            "' AND `phone` IS NOT NULL AND `phone` <> '')");
        if($records->Count>0){
            if(\Sys\Helper::$Security->ValidatePassword($password, $records->Row['password_hash'])){
                return $records;
            }
        }
        return new \Sys\DataBase\ActiveRecord($this->Reg, $this->Reg->DB->Provider);
    }    
    public function UserExists($email, $phone="", $login=null){
        if($email!=$phone && $email!=$login && $login!=$phone){
            if($login==null) $login="";
            $records = $this->Reg->DB->Query("SELECT * FROM `".$this->GetTableName()."` WHERE (`email`='".$this->Escape($email).
            "' AND `email` IS NOT NULL AND `email` <> '') OR (`username`='".$this->Escape($login).
            "' AND `username` IS NOT NULL AND `username` <> '') OR (`phone`='".$this->Escape($phone).
            "' AND `phone` IS NOT NULL AND `phone` <> '')");
            if($records->Count>0) return true;
        } else {
            return 2;
        }
        return 0;
    }    
    public function Add($data){
        $login = "";
        $email = "";
        $phone = "";
        $role = 6;
        $user_id = 1;
        $sales_id = 0; 
        $key = \Sys\Helper::$Security->TokenCreate();
        $pwd = \Sys\Helper::$Security->TokenCreate();        
        $hash = \Sys\Helper::$Security->PasswordHash($pwd);
        $path = '0,1';
        if(array_key_exists('email', $data)) $email = $data['email'];
        if(array_key_exists('username', $data)) $login = $data['username'];
        if(array_key_exists('phone', $data)) $phone = $data['phone'];
        if(array_key_exists('role_id', $data)) $role = (int)$data['role_id'];
        if(array_key_exists('user_id', $data)) {
            $user_id = (int)$data['user_id'];
            $parent = $this->GetById($user_id);
            if($parent->Count>0){
                $path=$parent->Row['path'];
                if(isset($path)) $path .=','.$user_id;
                else $path =$user_id;
            }
        }
        if(array_key_exists('sales_id', $data)) $sales_id = (int)$data['sales_id'];
        $sql="INSERT INTO `".$this->GetTableName()."` (`user_id`, `sales_id`, `role_id`, `path`, `email`, `username`, `phone`, `status`, `auth_key`, `password_hash`) VALUES ({$user_id}, {$sales_id}, {$role}, '{$path}', '".
            $this->Escape($email)."', '".$this->Escape($login)."', '".$this->Escape($phone)."', 1, '{$key}', '{$hash}')";
        $this->Reg->DB->Query($sql);
        return $pwd;
    }
    public function SetPwd($id, $old_pwd, $pwd, $confirm_pwd){
        if(isset($this->Reg->Session) && !array_key_exists('msg', $this->Reg->Session->Data)) $this->Reg->Session->Data['msg']=[];
        $user = $this->GetById($id);
        if($user->Count<1 || !\Sys\Helper::$Security->ValidatePassword($old_pwd, $user->Row['password_hash'])){
            if(isset($this->Reg->Session)){
                if(!array_key_exists('error', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['error']="";
                $this->Reg->Session->Data['msg']['error'] .= "Не верный старый пароль. ";
            }
            return;
        }
        if($pwd!=$confirm_pwd){
            if(isset($this->Reg->Session)){
                if(!array_key_exists('error', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['error']="";
                $this->Reg->Session->Data['msg']['error'] .= "Новый пароль и подтверждение пароля не совпадают. ";
            }
            return;
        }
        $hash = \Sys\Helper::$Security->PasswordHash($pwd);
        $sql="UPDATE `{$this->GetTableName()}` SET `password_hash` = '{$hash}' WHERE `id` = {$id}";
        $this->Reg->DB->Query($sql);
        if(isset($this->Reg->Session)){
            if(!array_key_exists('success', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['success']="";
            $this->Reg->Session->Data['msg']['success'] .= "Пароль успешно изменен. ";
        }
    }
    public function TimeUpdate($id){
        $sql="UPDATE `{$this->GetTableName()}` SET `updated_at` = CURRENT_TIMESTAMP WHERE `id` = {$id}";
        $this->Reg->DB->Query($sql);
    }    
}

