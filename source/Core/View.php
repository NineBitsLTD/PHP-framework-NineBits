<?php

namespace Core;
/** 
 * Формирует HTML контент
 * 
 * Класс содержит системные функции для формирования HTML контента.
 * 
 * @uses \Registry
 * @uses \Core\Object
 * @uses \Core\Path
 * @uses \Helper
 * @uses \Helper\Route
 * @uses \Helper\Json
 * 
 */
class View extends \Core\Object
{
    /**
     *
     * @var string Название используемой темы 
     */
    public $Theme = "Default";
    /**
     * Расширение файлов шаблонов
     * 
     * Используется при автоматическом формировании имени шаблона по маршруту
     * 
     * @var string
     */
    public $Extension = ".php";

    /**
     * 
     */
    public function __construct($theme=null) {
        if(isset($theme)) $this->Theme = $theme;
        parent::__construct([
            'Not found template for render: %s.'
        ]);
    }

    /**
     * Формирует HTML контент по указанному шаблону на основе переданных параметров
     * 
     * @param mixed $template Путь к шаблону
     * @param Array $data Перечень параметров передаваемых в шаблон
     * @param mixed $path_view Базовый путь к шаблону
     * @return string Подготовленный контент для вывода
     * @throws \Exception
     */
    public function Render($path, $data, $path_view=null) {
        $path = \Helper::$Route->ToClass($path, true, false);
        if($path_view!=null){
            $path_view = \Helper::$Route->ToClass($path_view);
        }
        $file = ($path_view!==null?$path_view: dirname(__DIR__).DIRECTORY_SEPARATOR.\Registry::$Path->View.($this->Theme!=''?DIRECTORY_SEPARATOR.$this->Theme:'')).DIRECTORY_SEPARATOR.$path.$this->Extension;
        if (file_exists($file)) {
            foreach ($data as $key => $value) {
                ${$key}=$value;
            }
            ob_start();
            require($file);
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
        $this->ErrorMsg(sprintf($this->ErrorList[0], $file), __FILE__, __LINE__);
    }
    /**
     * Формирует и отправляет Json контент
     * эта функция выполняет exit();
     * 
     * @param type $data Перечень передаваемых параметров
     */
    public function RenderJson($data=[]){
        header('Content-Type: application/json');
        echo \Helper::$Json->Encode($data);
        exit();
    }
    /**
     * Перенаправление текущей страницы по новому адресу
     * 
     * @param type $url Адрес для перенаправления
     * @param type $status Статус для перенаправления
     */
    public function Redirect($url, $status = 302) {
        header('Location: ' . str_replace(['&amp;', "\n", "\r"], ['&', '', ''], $url), true, $status); exit();
    }
    
}
