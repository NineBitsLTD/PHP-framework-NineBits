<?php
/**
 * Конфигурация проекта
 * 
 * Предназначен для структурирования проекта, включает в себя все необходимые компоненты
 * 
 * Закомментируйте ненужные компоненты или воспользуйтесь Генератором интерфейсов для настройки проекта.
 * 
 *  Пример использования: 
 *      require_once('../source/Registry.php');
 *      require_once('../source/Core/Autoloader.php');
 *      self::$Autoloader = new \Core\Autoloader([''=>'../source/'], "../source/Plugins");
 *      \Helper::$String = new \Helper\Str();
 *      \Helper::$Security = new \Helper\Security();
 *      \Helper::$File = new \Helper\File();
 *      self::$Path = new \Core\Path();
 *      self::$View = new \Core\View('');
 *      self::$Request = new \Core\Request('home','home','not_found');
 *      
 *      self::Dispatch();
 * 
 * @uses \Core\Autoloader
 * @uses \Core\Action
 * @uses \Core\Request
 * @uses \Helper
 * @uses \Helper\Str
 * @uses \Helper\Security
 * @uses \Helper\Html
 * 
 * @package \Sys
 * @author Vyacheslav Strikalo <ninebits@meta.ua>
 * @copyright (c) 2016, Nine Bits
 * @created 2016.12.02
 */
class Registry
{
    /**
     * Блок данных страницы
     * 
     * Данные предназначенные для обработки контроллером и последующей передачей в шаблон при формировании страницы.
     * 
     * @var array
     */
    public static $Data = [];
    /**
     * Автозагрузчик классов
     * 
     * Организовывает автозагрузку всех классов проекта
     * 
     * @var \Core\Autoloader
     */
    public static $Autoloader = null;    
    /**
     * Перечень папок проекта
     * 
     * Содержит перечень путей к папкам, из которых формируется структура проекта, относительно стартового индексного файла. 
     * 
     * @var \Core\Path
     */
    public static $Path = null;
    /**
     * Шаблонизатор HTML контента
     * 
     * @var \Core\View
     */
    public static $View = null;
    /**
     * Обработчик HTTP запроса.
     * 
     * @var \Core\Request
     */
    public static $Request = null;
    /**
     * Доступ к базе данных
     * 
     * Для подключения к еще одной базе создайте и инициализируйте аналогичное свойство.
     * 
     * @var \Core\DataBase 
     */
    public static $DB = null;
    /**
     * Сессия пользователя
     *
     * @var \Core\Session
     */
    public static $Session = null;
    /**
     * Главное действие запускающее контроллер формирующий страницу
     * 
     * @var \Core\Action
     */
    public static $Action = null;
    /**
     * Локализация страниц (языки)
     * 
     * @var \Core\Lng
     */
    public static $Lng = null;
    /**
     * Отправка писем
     * 
     * @var \Core\Mailer
     */
    public static $Mail = null;
    
    /**
     * Определение контроллера для запуска страницы
     * 
     * @param mixed $pathDefault Путь к контроллеру если переданный путь не указан
     * @param mixed $pathNotFound Путь к контроллеру если переданный путь ошибочный
     * @return mixed Результат выполнения контроллера по определенному пути
     */
    public static function Dispatch() {
        if(self::$Request!=null){
            if(self::$Session!=null && \Helper::$Array->KeyIsSet('msg', self::$Session->Data)) {
                $this->Data['msg'] = self::$Session->Data['msg'];
                unset(self::$Session->Data['msg']);
            }
            self::$Action = new \Core\Action(self::$Request->Path);            
            return self::$Action->Execute();
        }
        return null;
    }    
    /**
     * Формирует url к узлу сайта
     * 
     * @param mixed $path Путь к странице
     * @param mixed $request Параметры передаваемые странице
     * @return string
     */
    public static function Link($path="", $request=[]){
        if(self::$Request!=null){
            $root = self::$Request->Server['SCRIPT_NAME'];
            if(strpos($root, "index.php")!==false){
                $root = substr($root, 0, strpos($root, "index.php"));
            }
            $path = \Helper::$String->StrToPath($path,true,true);
            if(is_array($request) || is_object($request)){
                $request = http_build_query($request);
            }
            return self::$Request->Server['REQUEST_SCHEME']."://".self::$Request->Server['SERVER_NAME'].$root.$path.((string)$request==""?"":"?".$request);
        }
        return "";
    }
    /**
     * Выполнение произвольного метода произвольного контроллера по указанному пути
     * 
     * @param mixed $route Mаршрут к контроллеру
     * @param string $prefixClass Корень пространства имен контроллера
     * @param string $prefixMethod Приставка к имени метода контроллера
     * @param bool $error Если TRUE, то запрещает подстановку NotFound, когда класс контроллера не найден (требуется для исключения зацикливания перехода по NotFound)
     * @return mixed Возвращает результат выполнения метода
     */
    public static function Controller($route, $prefixClass=null, $prefixMethod=null, $error=false){        
        return (new Action($route, $prefixClass, $prefixMethod, $error))->Execute();
    }
    /**
     * Определяет послан ли запрос через Ajax
     * 
     * @return boolean
     */
    public static function IsAjax(){
        return (self::$Request!=null && array_key_exists('HTTP_X_REQUESTED_WITH',self::$Request->Server) && strtolower(self::$Request->Server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }
    /**
     * Определяет авторизирован ли пользователь
     * 
     * @return boolean Если пользователь авторизирован возвращает true, в противном случае false
     */
    public static function IsLogin(){
        return self::$Session!=null && self::$Session->IsLogin();
    }
    /**
     * Переводит ключь или массив ключей
     * 
     * @param mixed $key Ключь или массив ключей для перевода
     * @param string $code Трехбуквенный код языка, если не указан используется self::Code
     * @return mixed Возвращает переведенную строку или массив переводов без изменения ключей массива
     */
    public static function Translate($key, $code=null){
        if(isset(self::$Lng)){
            //if(!isset(self::$Lng)) self::$Lng = new \Config\Lng($this);
            //return self::$Lng->Translate($key, $code);
        }
        return $key;
    }
    /**
     * Определяет наличие словаря
     * 
     * @param string $code
     * @return bool
     */
    public static function LngExists($code){
        return (isset(self::$Lng) && self::$Lng->ExistsCode($code));
    }
}