<?php

namespace Controller;
/**
 * Базовый шаблон контроллера (раздел или страница сайта)
 * 
 * @uses \Registry
 * @uses \Core\Object
 * @uses \Core\Action
 * @uses \Helper
 * @uses \Helper\Route
 */
class Home extends \Core\Object{ 
    /**
     * Название раздел или страницы сайта
     * 
     * Данное название как правило соответствует части пути адресной строки и является алиасом $this->Action->PathShort
     * 
     * @var string
     */
    public $Page='';
    /**
     * Действие запустившее текущий метод данного контроллера
     * 
     * @var \Core\Action
     */
    public $Action = null;
    /**
     * Конструктор контроллера
     * 
     * @param \Core\Action $action
     */
    public function __construct($action = null) {
        $this->Action = $action;
        if($this->Action != null){
            $this->Page = $this->Action->PathShort;
        }
        parent::__construct();
    }
    /**
     * Воспроизведение шаблона страницы или блока
     * 
     * Воспроизведение шаблона по пути $route_template в виде HTML текста, с применением к нему параметров из массива $data
     * 
     * @param mixed $route_template
     * @param array $data
     * @return string
     */
    protected function render($route_template, $data = array()){
        return \Registry::$View->Render($route_template, $data);
    }
    /**
     * Генерация стандартных данных
     * 
     * Добавление в параметры для шаблонов страниц общих данных.
     * 
     * @param array $data
     */
    protected function setData(& $data){
        $data['ajax'] = \Registry::IsAjax();
        $data['base'] = \Registry::Link();
        $data['link'] = \Registry::Link($this->Action->Path);
        $data['theme'] = \Registry::$View->Theme;
        $data['page'] = str_replace(['/','\\'], '-', $this->Page);
        if(!$data['ajax']){
            $data['header'] = $this->render('header', $data);
            $data['footer'] = $this->render('footer', $data);
        }
    }

    public function methodIndex($data = array()) {
        $this->setData($data);
        echo $this->render($this->Action->PathShort.(mb_strtolower($this->Action->Method)!='index'?$this->Action->Method:''), $data);
    }
}
