<?php

namespace Controller;

class HeaderMenu extends \Sys\Controller {
    public function GetItems(){
        $result=[];
        $is_logged = $this->Reg->Session->IsLogged();        
        $this->AddItem($result, 'settings', 'HeaderMenu_Settings', $is_logged && $this->Reg->Session->User->Status>4, 'cog');
        $this->AddItem($result, 'home', 'HeaderMenu_Home', $is_logged && $this->Reg->Session->User->Status>4, 'home');
        $this->AddItem($result, 'guest', 'HeaderMenu_Guest', $is_logged && $this->Reg->Session->User->Status<5, 'home');
        $this->AddItem($result, 'dashboard', 'HeaderMenu_Dashboard', $is_logged && $this->Reg->Session->User->Status>4);
        $this->AddItem($result, 'sales', 'HeaderMenu_Sales', $is_logged && $this->Reg->Session->User->Status>4, 'podcast');
        $this->AddItem($result, 'offline_marketing', 'HeaderMenu_OfflineMarketing', $is_logged && $this->Reg->Session->User->Status>4, 'building-o');
        $this->AddItem($result, 'mailing', 'HeaderMenu_Mailing', $is_logged && $this->Reg->Session->User->Status>4, 'feed');
        $this->AddItem($result, 'news', 'HeaderMenu_News', $is_logged && $this->Reg->Session->User->Status>4, 'newspaper-o'); 
        $this->AddItem($result, 'training', 'HeaderMenu_Training', $is_logged && $this->Reg->Session->User->Status>4, 'question'); 
        $this->AddItem($result, 'statistics', 'HeaderMenu_Statistics', $is_logged && $this->Reg->Session->User->Status>4, 'bar-chart'); 
        $this->AddItem($result, 'profile', 'HeaderMenu_Profile', $is_logged, 'user-circle-o'); 
        $this->AddItem($result, 'webinars', 'HeaderMenu_Webinars', $is_logged && $this->Reg->Session->User->Status>4, 'video-camera'); 
        $this->AddItem($result, 'events', 'HeaderMenu_Events', $is_logged && $this->Reg->Session->User->Status>4, 'calendar'); 
        $this->AddItem($result, 'instruments', 'HeaderMenu_Instruments', $is_logged && $this->Reg->Session->User->Status>4, 'gavel');
        $this->AddItem($result, 'leaderboard', 'HeaderMenu_Leaderboard', $is_logged && $this->Reg->Session->User->Status>4, 'line-chart');
        $this->AddItem($result, 'reminders', 'HeaderMenu_Reminders', $is_logged && $this->Reg->Session->User->Status>4, 'bell-o');
        $this->AddItem($result, 'payment', 'HeaderMenu_Payment', $is_logged, 'money');
        return $result;
    }
    public function methodIndex($data = []) {
        $data['ajax']=true;
        $data['items']=$this->GetItems();
        return $this->RenderModify("", $data);
    }
}

