<?php

namespace Core;
/**
 * Базовый шаблон компонента
 * 
 * Предназначен для наследования всеми компонентами проекта, дает возможность описать базовые методы для всех объектов
 * 
 */
class Object{    
    /**
     * Получение публичных методов объекта $obj в виде массива
     * 
     * @param \Object $obj
     * @return array
     */
    static protected function getProperties($obj){
        $result=[];
        foreach ($obj as $key => $value) {
           $result[$key] = $value;
        }
        return $result;
    }
    /**
     * Перечень возможных сообщений об ошибках
     * 
     * @var array
     */
    public $ErrorList = [];
    /**
     * Шаблон для вывода текста ошибки
     * 
     * @var string
     */
    public $ErrorPattern = 'Error. %s in %s line %s.';
    
    /**
     * Конструктор объекта
     * 
     * @param mixed $list Перечень возможных сообщений с ошибками
     * @param string $pattern Шаблон для вывода текста ошибки
     */
    public function __construct($list = null, $pattern = null) {
        if(isset($pattern)) $this->ErrorPattern = $pattern;
        if(isset($list)) {
            if(is_array($list)) $this->ErrorList = $list; 
            else $this->ErrorList = [$list];
        }       
    }
    
    /**
     * Получить перечень всех методов
     * 
     * @return array
     */
    public function GetAllProperties(){
        $result=[];
        foreach ($this as $key => $value) {
           $result[$key] = $value;
        }
        return $result;
    }
    /**
     * Получить перечень публичных методов
     * 
     * @return array
     */
    public function GetPublicProperties(){
        return \Core\Object::getProperties($this);
    }
    /**
     * Генерация ошибки выбранной из переченя возможных сообщений об ошибках
     * 
     * @param int $code Индекс сообщения об ошибке
     * @param string $file Имя файла в котором возникла ошибка
     * @param string $line Номер строки файла в котором возникла ошибка
     * @throws \Exception
     */
    public function Error($code, $file, $line){
        throw new \Exception(sprintf($this->ErrorPattern, array_key_exists($code, $this->ErrorList)?$this->ErrorList[$code]:$code, $file, $line));
    }
    /**
     * Генерация ошибки с произвольным сообщением об ошибке
     * 
     * @param type $msg Сообщение об ошибке
     * @param type $file Имя файла в котором возникла ошибка
     * @param type $line Номер строки файла в котором возникла ошибка
     * @throws \Exception
     */
    public function ErrorMsg($msg, $file, $line){
        throw new \Exception(sprintf($this->ErrorPattern, $msg, $file, $line));
    }
}