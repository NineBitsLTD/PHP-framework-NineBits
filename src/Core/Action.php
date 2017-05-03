<?php

namespace Core;
/**
 * Действие запускающее контроллер (раздел сайта)
 * 
 * Данный компонент требуется для запуска метода контроллера заданного через адресную строку браузера.
 * 
 * @uses \Registry
 * @uses \Core\Object
 * @uses \Core\Request
 * @uses \Helper
 * @uses \Helper\Str
 */
class Action extends \Core\Object 
{
    /**
     * Разделитель пространств имен в полном имени класса
     * 
     * @var string
     */
    public static $NamespaceSeparator = '\\';
    /**
     * Реальный путь текущего действия
     * 
     * Путь переданный в адресной строке может содержать, или не содержать в себе - код языка,
     * или имя метода контроллера. Но обязательно содержит имя контроллера.
     * 
     * @var string
     */
    public $Path = '';
    /**
     * Короткий путь текущего действия
     * 
     * Путь переданный в адресной строке после обработки 
     * не содержит имени метода контроллера и кода языка, 
     * может совпадать или не совпадать с реальным, 
     * но не совпадает с длинным.
     * 
     * @var string 
     */
    public $PathShort = '';
    /**
     * Длинный путь текущего действия
     * 
     * Путь переданный в адресной строке после обработки
     * содержит имя метода, но не содержит кода языка,
     * может совпадать или не совпадать с реальным,
     * но не совпадает с коротким.
     * 
     * @var string
     */
    public $PathFull = '';
    /**
     * Имя класса контроллера без префикса.
     * 
     * @var string
     */
    public $Class = '';
    /**
     * Корень пространства имен контроллера.
     * 
     * Дополняется к прастранству имен класса контроллера перед его поиском, или созданием его экземпляра.
     * 
     * @var string
     */
    public $PrefixClass ='Controller';
    /**
     * Имя метода контроллера без префикса.
     * 
     * @var string
     */
    public $Method = 'Index';
    /**
     * Приставка к имени метода контроллера. 
     * 
     * Дополняется к имени метода перед его поиском или выполнением.
     * 
     * @var type 
     */
    public $PrefixMethod ='Method';
    /**
     * Язык сайта.
     * 
     * Система словарно-грамматических средств определенная по трехбуквенному коду в начале пути.
     * 
     * Если в начале пути встречается трехбуквенный код языка,
     * после которого остаток пути указывает на существующий контроллер или метод контроллера,
     * но весь путь с кодом языка не указывает на какой-либо контроллер или метод контроллера, 
     * то данный код воспринимается как код языка.
     * 
     * @var string
     */
    public $Lng = '';
    
    /**
     * Конструктор Действия
     * 
     * @param mixed $route Mаршрут к контроллеру
     * @param string $prefixClass Корень пространства имен контроллера
     * @param string $prefixMethod Приставка к имени метода контроллера
     * @param bool $error Если TRUE, то запрещает подстановку NotFound, когда класс контроллера не найден (требуется для исключения зацикливания перехода по NotFound)
     */
    public function __construct($route, $prefixClass = null, $prefixMethod = null, $error=false) {
        if(isset($prefixClass)) $this->PrefixClass = $prefixClass;
        if(isset($prefixMethod)) $this->PrefixMethod = $prefixMethod;
        parent::__construct(\Registry::Translate([
            'Error: Class \'%s\' not found !',
            'Error: Calls \'%s\' to methods %s are not allowed!',
            'Error: Class \'%s\' and \'%s\' not found !'
        ]), \Registry::Translate($this->ErrorPattern));
        $this->ParseRoute($route, $error);
    }

    /**
     * Запуск метода контроллера с передачей параметров
     * 
     * @return $mixed Возвращает результат выполнения метода
     */
    public function Execute() {  
        $class = $this->PrefixClass .self::$NamespaceSeparator. $this->Class;
        if (!class_exists($class)) { 
            $this->ErrorMsg(sprintf($this->ErrorList[0], $class), __FILE__, __LINE__);
        } else {
            $controller = new $class($this);
        }
        $reflection = new \ReflectionClass($class);
        if(!$reflection->hasMethod($this->PrefixMethod.$this->Method)) $this->Method = 'Index';
        if($reflection->hasMethod($this->PrefixMethod.$this->Method) &&  $reflection->getMethod($this->PrefixMethod.$this->Method)->getNumberOfRequiredParameters() == 0 ) {
            return call_user_func_array(array($controller, $this->PrefixMethod.$this->Method));
        } else {
            $this->ErrorMsg(sprintf($this->ErrorList[1], $this->PrefixClass .self::$NamespaceSeparator. $this->Class, $this->PrefixMethod.$this->Method), __FILE__, __LINE__);
        }
    }
    /**
     * Заполнение параметров действия
     * 
     * Определение контроллера, его метода и кода языка сайта по заданному пути
     * 
     * @param mixed $route Часть адресной строки браузера, путь к странице
     * @param bool $error Флаг запрещающий подстановку NotFound если класс контроллера не найден
     * @throws \Exception
     */
    protected function ParseRoute($route, $error=false){
        if($route=='') {
            if(\Registry::IsLogin()) $route = \Registry::$Request->PathStart;
            else $route = \Registry::$Request->PathDefault;
        } else $route = \Helper::$String->StrToPath($this->PrefixClass, false, false);
        $this->PrefixClass = \Helper::$String->StrToClass($this->PrefixClass, true, false, self::$NamespaceSeparator);
        $class = \Helper::$String->StrToClass($route, false, false);
        // Ищем класс соответствующий указанному полному пути
        if (class_exists($this->PrefixClass .self::$NamespaceSeparator. $class)) {
            $this->Class = $class;
            $this->Method = 'Index';
            $this->Path = $route;
            $this->PathShort = $this->Path;
            $this->PathFull = $this->PathShort.DIRECTORY_SEPARATOR. mb_strtolower($this->Method);
            return true;
        }
        $class_tmp = explode(self::$NamespaceSeparator, $class);
        // Ищем класс соответствующий указанному пути за исключением последнего слова пути,
        // которое определяет метод
        if(count($this->Path)>1) {
            $this->Method = array_pop($class_tmp);
            $class_tmp = implode(self::$NamespaceSeparator, $class_tmp);
            if(class_exists($this->PrefixClass .self::$NamespaceSeparator. $class_tmp)){
                $this->Class = $class_tmp;
                $this->Path = $route;
                $this->PathShort = \Helper::$String->StrToPath($class_tmp, false, false);
                $this->PathFull = $this->Path;
                return true;
            }
        }
        $class_tmp = explode(self::$NamespaceSeparator, $class);
        if(count($class_tmp)>1) {
            $this->Lng = mb_strtolower(array_shift($class_tmp));
            if(\Registry::LngExists($this->Lng)){
                $class_tmp = implode(self::$NamespaceSeparator, $class_tmp);
        // Ищем класс соответствующий указанному пути за исключением первого слова пути,
        // которое определяет язык перевода
                if(class_exists($this->PrefixClass .self::$NamespaceSeparator. $class_tmp)){
                    $this->Class = $class_tmp;
                    $this->Method = 'Index';
                    $this->Path = \Helper::$String->StrToPath($class_tmp, false, false);
                    $this->PathShort = $this->Path;
                    $this->PathFull = $this->PathShort.DIRECTORY_SEPARATOR. mb_strtolower($this->Method);
                    \Registry::$Lng->SetCode($this->Lng);
                    return true;
                }
                $class_tmp = explode(self::$NamespaceSeparator, $class_tmp);
        // Ищем класс соответствующий указанному пути за исключением первого слова пути,
        // которое определяет язык перевода и последнего слова пути, которое определяет метод
                if(count($class_tmp)>1) {
                    $this->Method = array_pop($class_tmp);
                    $class_tmp = implode(self::$NamespaceSeparator, $class_tmp);
                    if(class_exists($this->PrefixClass .self::$NamespaceSeparator. $class_tmp)){
                        $this->Class = $class_tmp;
                        $this->PathShort = \Helper::$String->StrToPath($class_tmp, false, false);
                        $this->PathFull = $this->Path.DIRECTORY_SEPARATOR. mb_strtolower($this->Method);
                        $this->Path = $this->PathFull;
                        \Registry::$Lng->SetCode($this->Lng);
                        return true;
                    }
                }
            }
        } else if(count($class_tmp)==1 && $route!=\Registry::$Request->PathStart && 
                $route!=\Registry::$Request->PathDefault && $route!=\Registry::$Request->PathNotFound){
            $this->Lng = mb_strtolower($class_tmp[0]);
            if(\Registry::IsLogin()) $class_tmp = \Helper::$String->StrToClass(\Registry::$Request->PathStart);
            else $class_tmp = \Helper::$String->StrToClass(\Registry::$Request->PathDefault);
        // Предполагаем что указанный путь определяет только язык перевода,
        // ищем класс соответствующий пути указанному по умолчанию
            if(class_exists($this->PrefixClass .self::$NamespaceSeparator. $class_tmp) && \Registry::LngExists($this->Lng)){
                $this->Class = $class_tmp;
                $this->Method = 'Index';
                $this->Path = \Helper::$String->StrToPath($class_tmp, false, false);
                $this->PathShort = $this->Path;
                $this->PathFull = $this->PathShort.DIRECTORY_SEPARATOR. mb_strtolower($this->Method);
                \Registry::$Lng->SetCode($this->Lng);
                return true;
            }
        }
        if(!$error){
            $this->ParseRoute(\Registry::$Request->PathNotFound, true);
        } else { 
            $this->ErrorMsg(sprintf($this->ErrorList[2], $class, \Registry::$Request->PathNotFound), __FILE__, __LINE__);          
        }
    }
}
