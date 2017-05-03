<?php
namespace Helper;

/**
 * Операции с файлами
 * 
 */
class File
{
    /**
     *
     * @var \Helper\File\ParserPHP
     */
    public $PHP = null;
    
    public function __construct() {
        if(class_exists('\Helper\File\ParserPHP')) $this->PHP = new \Helper\File\ParserPHP();
    }

    /**
     * 
     * @param mixed $value
     * @return string
     */
    public function IntToByteUnit($value){
        $units = "байт";
        $value = (float)$value;
        if($value>1024) {
            $value = $value/1024;
            $units = "Кб";
        }
        if($value>1024) {
            $value = $value/1024;
            $units = "Мб";
        }
        if($value>1024) {
            $value = $value/1024;
            $units = "Гб";
        }
        if($value>1024) {
            $value = $value/1024;
            $units = "Тб";
        }
        return number_format($value, 2, '.', '')." ".$units;
    }    

}