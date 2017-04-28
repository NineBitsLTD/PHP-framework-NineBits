<?php
namespace Helper;

/**
 * Операции над массивами
 */
class Arr
{
    /**
     * Проверка на существование массива и ключа в нем
     * 
     * @param string $key Ключ елемента массива
     * @param array $arr Массив с данными
     * @return boolean
     */
    public static function KeyIsSet($key, $arr){
        return is_array($arr) && array_key_exists($key, $arr);
    }
    /**
     * Поиск по значению указанного поля
     * 
     * @param type $arr
     * @param type $name
     * @param type $field_name
     * @return type
     */
    public function FindByField($arr, $name, $field_name){
        if(is_array($arr)) foreach ($arr as $key => $value) if(is_array($value) && array_key_exists($field_name, $value) && $value[$field_name]==$name) return $value[$field_name];
        return null;
    }
    /**
     * Получить колонку $column_value из многомерного массива $arr и назначить ключи из колонки $column_key,
     * если ключи в колонке $column_key не уникальные все повторяющиеся будут перезаписаны последним значением,
     * 
     * 
     * @param array $arr Входной массив
     * @param string $column_value Название колонки со значениями
     * @param string $column_key Название колонки с ключами, если не указано беруться ключи рядка, если ключ не строка, за ключ будет принят ключ рядка.
     * @return array Колонка $column_key из многомерного массива
     */
    public function GetColumnMultiarray($arr, $column_value, $column_key=''){
        $result = [];
        if(is_array($arr) && count($arr)>0 && is_array(current($arr)) && array_key_exists($column_value, current($arr))) {
            foreach ($arr as $key => $value) if(is_array($value) && array_key_exists($column_value, $value)){
                if($column_key!='' && array_key_exists($column_key, $value) && is_string($value[$column_key])) $result[$value[$column_key]] = $value[$column_value];
                else $result[$key] = $value[$column_value];
            }
        }
        return $result;
    }
    /**
     * Преобразование массив в котором, ключ id, а значение parent_id, 
     * в массив где ключ id а значение путь к id в виде перечня parent_id через запятую
     * (id и parent_id - целые числа)
     * 
     * @param array $arr
     * @return array
     */
    public function ArrayToTree($arr){
        $result = [];
        foreach ($arr as $id => $parent) {
            $result[$id] = $this->CreatePath($arr, (int)$id, 0);
        }
        uasort ($result , function ($a, $b) { return strnatcmp($a,$b); } );
        return $result; 
    }
    
    private function CreatePath($arr, $id, $step,  $path=""){
        $path = ($path!="")?(int)$id.",".$path:(int)$id;
        $path_arr = explode(",", $path);
        if(array_key_exists($id, $arr) && !in_array($arr[$id], $path_arr)) {
            return $this->CreatePath($arr, (int)$arr[$id], $step+1, $path);
        } else return $path;
    }
}