<?php

namespace Core;
/**
 * Обработка входящего запроса и данных (Формирование маршрута загрузки контроллера)
 * 
 * Компонент содержит системные функции для поиска контроллера и передачи в него данных из запроса.
 * 
 * @uses \Helper
 * @uses \Helper\Security
 */
class Request extends \Core\Object
{
    /**
     * Адресная строка
     * 
     * @var string 
     */
    public $Url = "";
    /**
     * Путь из адресной строки
     * 
     * @var string
     */
    public $Path = "";
    /**
     * Путь по умолчанию
     * 
     * @var string
     */
    public $PathStart = "";
    /**
     * Путь по умолчанию если пользователь авторизировался
     * 
     * @var string
     */
    public $PathDefault = "";
    /**
     * Путь по умолчанию если адрес не найден.
     * 
     * @var string
     */
    public $PathNotFound = "";
    /**
     * Обработанный масив $_GET
     * 
     * @var Array
     */
    public $Get = [];
    /**
     * Обработанный масив $_POST
     * @var Array
     */
    public $Post = [];
    /**
     * Обработанный масив $_REQUEST
     * 
     * @var Array
     */
    public $Request = [];
    /**
     * Обработанный масив $_COOKIE
     * 
     * @var Array
     */
    public $Cookie = [];
    /**
     * Обработанный масив $_FILES
     * 
     * @var Array
     */
    public $Files = [];
    /**
     * Обработанный масив $_SERVER
     * 
     * @var Array
     */
    public $Server = [];
    /**
     * 
     * @param string $pathStart
     * @param string $pathDefault
     * @param string $pathNotFound
     */
    function __construct($pathStart=null, $pathDefault=null, $pathNotFound=null) {
        parent::__construct();
        $this->Get = $_GET;
        $this->Post = $_POST;
        $this->Request = $_REQUEST;
        $this->Cookie = $_COOKIE;
        $this->Files = $_FILES;
        $this->Server = $_SERVER;
        $this->Url = $this->Server['REQUEST_SCHEME']."://".$this->Server['SERVER_NAME'].$this->Server['REQUEST_URI'];
        if(array_key_exists('route', $this->Request)) $this->Path = \Helper::$String->StrToPath($this->Request['route'], false, false);
        if(isset($pathStart)) $this->PathStart = \Helper::$String->StrToPath($pathStart, false, false);
        if(isset($pathDefault)) $this->PathDefault = \Helper::$String->StrToPath($pathDefault, false, false);
        if(isset($pathNotFound)) $this->PathNotFound = \Helper::$String->StrToPath($pathNotFound, false, false);
    }
}

