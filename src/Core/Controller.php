<?php

namespace Core;
/**
 * Базовый шаблон контроллера, формирует логику раздела или страницы сайта
 * 
 * @uses \Registry
 * @uses \Core\Object
 * @uses \Core\Action
 * @uses \Helper
 * @uses \Helper\Str
 */
class Controller extends \Core\Object {
    /**
     * Название раздела или страницы сайта
     * 
     * Данное название как правило соответствует части пути адресной строки и является алиасом $this->Action->PathShort
     * 
     * @var string
     */
    public $Page = "";
    /**
     * Действие запустившее контроллер
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
     * Адресная строка (след из хлебных крошек)
     * 
     * @param array $route Путь к странице
     * @param array $dictionary Словарь переводов ключей пути
     * @return array Вложенный массив содержащий массивы ссылок и названий
     */
    protected function breadcrumbs($route) {
        $route = \Helper::$String->StrToClass($route);
        $breadcrumbs = ['Home'=>[
            'href'=>\Registry::Link('Home'),
            'text'=>\Registry::Translate('HeaderMenu_Home')
        ]];
        $path = '';
        $text = '';
        $sep = '';
        $i = 0;
        foreach ($route as $value) {
            $path .= $sep.$value;
            $text .= $value;
            $breadcrumbs[$value]=[
                'text'=>\Registry::Translate("HeaderMenu_".$text)                
            ];
            if($i<(count($route)-1)){                
                $breadcrumbs[$value]['href']=\Registry::Link($path);
            }
            $sep = "/";
            $i++;
        }
        return $breadcrumbs;
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
        $data['page'] = $this->Page;
        $data['page_name'] = str_replace(['/','\\'], '-', $this->Page);
        if(!$data['ajax']){
            $data['header'] = $this->render('header', $data);
            $data['footer'] = $this->render('footer', $data);
        }
    }

    public function MethodIndex() {
        $this->setData(\Registry::$Data);
        echo $this->render($this);
    }
    
}

