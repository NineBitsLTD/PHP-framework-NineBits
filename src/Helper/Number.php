<?php
namespace Helper;

/**
 * Операции с числами
 */
class Number{
    /**
     * Преобразовать число в масив бит
     * 
     * @param mixed $number Исходное число
     * @param int $length Длинна масива бит, по умолчанию 5
     * @return array Массив бит
     */
    public function NumberToBitArr($number, $length=5){
        if(is_array($number) || is_object($number)) $number=0;
        if(is_string($number) || is_float($number)) $number=(int)$number;
        return str_split(str_pad(strrev(decbin($number)), $length, '0'));
    }
    /**
     * Преобразовать масив бит в число
     * 
     * @param array $arr Массив бит
     * @return int Число
     */
    public function BitArrToNumber($arr){
        if(is_array($arr)){
            return bindec(strrev(preg_replace('/[^01]/','0',implode('', $arr))));
        } else return 0;
    }
    /**
     * Задать значение бита в числе
     * 
     * @param mixed $value
     * @param mixed $number
     * @param int $pos
     */
    public function SetBitInt($value, $pos, $number){
        $arr = $this->IntToBitArr($number, (int)$pos+1);
        $arr[(int)$pos] = $value?1:0;        
        return $this->BitArrToInt($arr);
    }
    /**
     * Получить значение бита в числе
     * 
     * @param int $pos 
     * @param mixed $number
     * @param int $length
     * @return boolean
     */
    public function GetBitInt($pos, $number){
        $arr = $this->NumberToBitArr($number);
        if(count($arr)<=(int)$pos) return false;
        return ($arr[$pos]==1);
    }
}

