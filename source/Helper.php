<?php
/**
 * Набор вспомогательных функций
 * 
 * @uses \Helper\Arr
 * @uses \Helper\File
 * @uses \Helper\Html
 * @uses \Helper\Json
 * @uses \Helper\Route
 * @uses \Helper\Security
 * @uses \Helper\Str
 * 
 */
class Helper
{  
    /**
     * Операции со строками
     * 
     * @var \Helper\Str
     */
    public static $String = null;
    /**
     * Операции с числами
     * 
     * @var \Helper\Number
     */
    public static $Number = null;
    /**
     * Операции над массивами
     * 
     * @var \Helper\Arr
     */
    public static $Array = null;
    /**
     * Операции для обеспечения безопасности
     * 
     * @var \Helper\Security
     */
    public static $Security = null;
    /**
     * Работа с данными формата Json
     * 
     * @var \Helper\Json
     */
    public static $Json = null;
    /**
     * Операции с файлами
     * 
     * @var \Helper\File
     */
    public static $File = null;
    /**
     * Операции с HTML контентом
     * 
     * @var \Helper\Html
     */
    public static $Html = null;
    /**
     * Операции с путями загрузки страниц
     * 
     * @var \Helper\Route
     */
    public static $Route = null;
    /**
     * Операции над датами
     * 
     * @var \Helper\Date
     */
    public static $Date = null;
}
