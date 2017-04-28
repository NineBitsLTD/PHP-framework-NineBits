<?php

namespace User;
/**
 * Информация пользователя
 * 
 * @uses \Sys\Config\Mailer
 * 
 */
class Controller extends \Sys\Controller { 
    
    public function methodIndex($data = array()) {
        if(array_key_exists('login', $this->Reg->Request->Post) && 
           array_key_exists('password', $this->Reg->Request->Post)){
            $this->Reg->Session->User->Login($this->Reg->Request->Post['login'], $this->Reg->Request->Post['password']);
            if($this->Reg->Session->IsLogged()){
                $this->Reg->Redirect($this->Reg->Request->PathDefault);
            }
        }
        $data['base'] = $this->Reg->Link();
        $data['theme'] = $this->Reg->View->Theme;
        $data['link_password_reset'] = $this->Reg->Link(\Sys\Helper::$Route->Normalize($this->Reg->Request->PathStart)."/password_reset");
        $data['link_signup'] = $this->Reg->Link(\Sys\Helper::$Route->Normalize($this->Reg->Request->PathStart)."/signup");
        $data['link_login'] = $this->Reg->Link(\Sys\Helper::$Route->Normalize($this->Reg->Request->PathStart)."/login");
        $data['ajax']=$this->Reg->IsAjax();
        echo $this->Render($data);
    }
    public function methodLogin($data = array()) {
        $this->Action->Method = "Index";
        $this->methodIndex($data);
    }
    public function methodSignup($data = array()) {
        $this->Reg->Session->Data['msg'] = [];
        if(array_key_exists('email',$this->Reg->Request->Post) &&
            array_key_exists('phone',$this->Reg->Request->Post)){
            $modelUser = new \User\Model\User($this->Reg);
            $code = $modelUser->UserExists($this->Reg->Request->Post['email'], $this->Reg->Request->Post['phone']);
            if((int)$code>0){
                $this->Reg->Session->Data['msg']["error"] = $this->Reg->Translate('PageAuthMsg'.$code);
            } else {
                $pwd = $modelUser->Add([
                    'user_id'=>(array_key_exists('sponsor',$this->Reg->Request->Post)?(int)$this->Reg->Request->Post['sponsor']:0),
                    'sales_id'=>(array_key_exists('sales',$this->Reg->Request->Post)?(int)$this->Reg->Request->Post['sales']:1),
                    'email'=>$this->Reg->Request->Post['email'],
                    'phone'=>$this->Reg->Request->Post['phone']
                ]);
                $msg = $this->Reg->Mail->Send($this->Reg->Request->Post['email'], null, "Invicta:".$this->Reg->Link().". You password: ".$pwd);
                //$this->Reg->Session->Data['msg']["success"] = $this->Reg->Translate('PageAuthMsg0');
                if($msg!='') {
                    $this->Reg->Session->Data['msg']["error"] .= " ".$msg;                    
                } else {
                    $this->Reg->Request->Post['login']=$this->Reg->Request->Post['email'];
                    $this->Reg->Request->Post['password']=$pwd;
                    $this->methodLogin($data);
                }
            }
        }
        $data['link_signup'] = $this->Reg->Link(\Sys\Helper::$Route->Normalize($this->Reg->Request->PathStart)."/signup");
        $data['link'] = $this->Reg->Link(\Sys\Helper::$Route->Normalize($this->Reg->Request->PathStart));
        $this->methodIndex($data);
    }
    public function methodPasswordReset($data = array()) {
        $this->methodIndex($data);
    }
    public function methodLoginFacebook($data = array()) {
        $this->methodIndex($data);
    }
    public function methodLoginGoogle($data = array()) {
        $this->methodIndex($data);
    }
    public function methodLoginOdnoklassniki($data = array()) {
        $this->methodIndex($data);
    }
    public function methodLoginVk($data = array()) {
        $this->methodIndex($data);
    }
    public function methodLogout($data = array()) {
        if($this->Reg->Session->IsLogged()) $this->Reg->Session->User->Logout();
        $this->Reg->Redirect();
    }
}
