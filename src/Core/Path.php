<?php

namespace Core;
/** 
 * Конфигурирование путей папок.  
 * 
 * Содержит перечень путей к папкам, из которых формируется структура проекта, относительно стартового индексного файла. 
 * 
 * Свойства могут быть добавлены или изменены по вашим требованиям. 
 */
class Path extends \Core\Object 
{
    /**
     * Системная - папка содержащая всю объектно-ориентированную модель проекта (все классы) требуемые для работы сайта.
     * @var string 
     */
    public $Root = 'src';
    /**
     * Контроллеры
     * 
     * @var string
     */
    public $Controller = 'Controller';
    /**
     * Модели
     * 
     * @var string
     */
    public $Model = 'Model';
    /**
     * Шаблоны - папка содержащая все шаблоны для формирования веб страниц.
     * @var string 
     */
    public $View = 'View';
    /**
     * Общедоступная Веб - папка содержащая изображения, скрипты, шрифты, стили и файлы доступные для загрузки или запуска через браузер клиента.
     * @var string 
     */
    public $Web = '';
    /**
     * Плагины - папка содержащая внешние компоненты для использования в проекте
     * 
     * @var string
     */
    public $Plugins = 'Plugins';
    /**
     * Виджеты - папка содержащая виджеты (контроллеры формирующие специализированные html блоки)
     * 
     * @var string
     */
    public $Widget = 'Plugins/Widget';
    /**
     * Временная - папка для временных файлов
     * @var string 
     */
    public $Tmp = 'tmp';
    
    function __construct() {
        $this->Root = dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.$this->Root;
        $this->Tmp = dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.$this->Tmp;
        $this->Web = dirname(dirname(__DIR__)).DIRECTORY_SEPARATOR.$this->Web;
        $this->Controller = $this->Root.DIRECTORY_SEPARATOR.$this->Controller;
        $this->Model = $this->Root.DIRECTORY_SEPARATOR.$this->Model;
        $this->View = $this->Root.DIRECTORY_SEPARATOR.$this->View;
        $this->Plugins = $this->Root.DIRECTORY_SEPARATOR.$this->Plugins;
        $this->Widget = $this->Root.DIRECTORY_SEPARATOR.$this->Widget;
    }
}

