<?php

namespace Sys\Config;
/** 
 * Формирует HTML контент
 * 
 * Класс содержит системные функции для формирования HTML контента. 
 */
class DataBase extends \Sys\Object
{
    /**
     * Адресс сервера базы данных
     * 
     * @var string
     */
    public $Host = "127.0.0.1";
    /**
     * Номер порта базы данных
     * @var int
     */
    public $Port = '3306';
    /**
     * Префикс таблиц базы данных
     * 
     * @var string
     */
    public $Prefix = "";
    /**
     * Имя базы данных
     * 
     * @var string
     */
    public $DBName = "bmu";
    /**
     * Имя пользователя базы данных
     * 
     * @var string
     */
    public $User = "bmu";
    /**
     * Пароль пользователя базы данных
     * 
     * @var string
     */
    public $Password = "12345678";
    /**
     * Кодировка базы данных
     * 
     * @var string
     */
    public $Charset = "utf8";
}

