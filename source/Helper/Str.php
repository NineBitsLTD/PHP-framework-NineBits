<?php
namespace Helper;

/**
 * Операции со строками
 */
class Str
{
    /**
     * Операции со строками
     * 
     * @param string $string Cтрока измеряемая по длине
     * @return integer Число байтов в данной строке.
     */
    public function ByteLength($string) {
        return mb_strlen($string, '8bit');
    }
    /**
     * Выполняет сравнение строк с использованием атаки синхронизации устойчивый подход.
     *  
     * @see http://codereview.stackexchange.com/questions/13512
     * @param string $expected Строка для сравнения.
     * @param string $actual Строка поставляемая пользователем.
     * @return boolean Равны ли строки.
     */
    public function СompareString($expected, $actual) {
        $expected .= "\0";
        $actual .= "\0";
        $expectedLength = $this->ByteLength($expected);
        $actualLength = $this->ByteLength($actual);
        $diff = $expectedLength - $actualLength;
        for ($i = 0; $i < $actualLength; $i++) {
            $diff |= (ord($actual[$i]) ^ ord($expected[$i % $expectedLength]));
        }
        return $diff === 0;
    }    
    /**
     * Преобразовывает строку в путь к файлу
     * Любую косую заменяет на $separator. 
     * Убирает $separator спереди если он есть и он не единственный символ.
     * 
     * @param mixed $path Путь который следует нормализовать
     * @param string $slash_start Наличие слеша в начале, если true - добавляет слеш, если false - убирает, иначе ничего не делает. По умолчанию null.
     * @param string $slash_end Наличие слеша в конце, если true - добавляет слеш, если false - убирает, иначе ничего не делает. По умолчанию null.
     * @return string Нормализованный путь
     */
    public function PathNormalize($route, $slash_start = null, $slash_end = null, $separator = DIRECTORY_SEPARATOR){
        if(is_array($route)) $route = implode($separator, $route);
        $route = str_replace("\\", "/", (string)$route);
        $route = preg_replace('![^a-zA-Z0-9_./-]!', '', trim((string)$route));
        if($separator!="/") $route = str_replace("/", $separator, (string)$route);
        if(mb_strlen($route)>0 && $route[0] == $separator) { 
            $route = mb_substr($route, 1); 
            if(!isset($slash_start)) $slash_start = true;            
        }
        if(mb_strlen($route)>0 && $route[mb_strlen($route)-1] == $separator) {
            $route = mb_substr(0, mb_strlen($route)-1);            
            if(!isset($slash_end)) $slash_end = true;
        }
        if(mb_strlen($route)>0 && $slash_start===true) $route = $separator.$route;
        if(mb_strlen($route)>0 && $slash_end===true) $route .= $separator;
        if(mb_strlen($route)==0 && $slash_start===true && $slash_end===true) $route = $separator;
        return $route;
    }
    /**
     * Преобразование строки или пути в имя класса. Пример Controller\NotFound
     * 
     * @param mixed $route
     * @return string
     */
    public function StrToClass($route, $slash_start = null, $slash_end = null){
        $route = $this->StrToPath($route, $slash_start, $slash_end);
        $route = str_replace('-', '_', $route);
        $route = preg_replace('/[^a-zA-Z0-9_\/]/', '', $route);
        $className = explode(DIRECTORY_SEPARATOR, $route);
        $i=0;
        foreach ($className as $key => $value) {
            $pieces = explode('_', $value);        
            foreach ($pieces as $pkey => $piece) {
                $pieces[$pkey] = ucfirst(strtolower($piece));
            }
            $className[$key] = implode('', $pieces);
            $i++;
        }
        return $this->PathNormalize($className, $slash_start, $slash_end, "\\");
    }
    /**
     * Преобразование строки или имени класса в путь. Пример controller/not_found
     * 
     * @param mixed $className
     * @return array
     */
    public function StrToPath($className, $slash_start = null, $slash_end = null){
        $className = $this->PathNormalize($className, $slash_start, $slash_end);
        $route = explode(DIRECTORY_SEPARATOR, $className);
        foreach ($route as $key => $value) {
            $pieces = "";
            foreach (preg_split('//u',$value,-1,PREG_SPLIT_NO_EMPTY) as $ckey=>$char) {
                if(ctype_upper($char) && $ckey>0) $pieces .="_".$char;
                else $pieces .=$char;
            }
            $route[$key] = strtolower($pieces);
        }
        return $this->PathNormalize($route, $slash_start, $slash_end, DIRECTORY_SEPARATOR);
    }    
}