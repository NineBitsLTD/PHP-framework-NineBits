<?php

namespace User;

class Role extends \Sys\Object {
    public $Id=0;
    /**
     *
     * @var string 
     */
    public $Title=0;
    /**
     *
     * @var int 
     */
    public $Status=0;
    /**
     * Права пользователя
     * 
     * @var array
     */
    public $Rights=[];   
    /**
     * Права пользователя
     * 
     * @var \User\Model\Role
     */
    protected $Model=""; 
    /**
     * Права пользователя
     * 
     * @var array
     */
    protected $Data="";  
    /**
     * Инициализация роли
     * 
     * @param \Sys\Registry $registry Конфигурация проекта
     */
    public function __construct(&$registry, $id=0) {
        parent::__construct($registry);        
        $this->Model = new \User\Model\Role($this->Reg);
        $this->Id = $id;
        $this->Data = $this->Model->GetById($this->Id)->Row;
        if(array_key_exists('title', $this->Data)) $this->Title = $this->Reg->Translate($this->Data['title']);
        if(array_key_exists('status', $this->Data)) $this->Status = $this->Data['status'];
        if(array_key_exists('rights', $this->Data)) $this->Rights = explode (',', $this->Data['rights']);
    }
    
    public function Check($route){
        $route = \Sys\Helper::$Route->StrFromClass($route);        
        //var_dump([$route, $this->Rights]);
        return ($this->Id==1 || in_array($route, $this->Rights) || in_array($route."/index", $this->Rights));
    }
}

