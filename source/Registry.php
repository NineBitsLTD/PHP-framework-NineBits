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
 *      \Registry::$Autoloader = new \Core\Autoloader([''=>'../source/'], "../source/Plugins");
 *      \Helper::$String = new \Helper\Str();
 *      \Helper::$Security = new \Helper\Security();
 *      \Helper::$File = new \Helper\File();
 *      \Registry::$Path = new \Core\Path();
 *      \Registry::$View = new \Core\View('');
 *      \Registry::$Request = new \Core\Request('home','home','not_found');
 *      
 *      \Registry::Dispatch();
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
        if(\Registry::$Request!=null){
            if(\Registry::$Session!=null && array_key_exists('msg', \Registry::$Session->Data)) {
                $this->Data['msg'] = \Registry::$Session->Data['msg'];
                unset(\Registry::$Session->Data['msg']);
            }
            \Registry::$Action = new \Core\Action(\Registry::$Request->Path);            
            return \Registry::$Action->Execute(self::$Data);
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
        if(\Registry::$Request!=null){
            $root = \Registry::$Request->Server['SCRIPT_NAME'];
            if(strpos($root, "index.php")!==false){
                $root = substr($root, 0, strpos($root, "index.php"));
            }
            $path = \Helper::$String->StrToPath($path,true,true);
            if(is_array($request) || is_object($request)){
                $request = http_build_query($request);
            }
            return \Registry::$Request->Server['REQUEST_SCHEME']."://".\Registry::$Request->Server['SERVER_NAME'].$root.$path.((string)$request==""?"":"?".$request);
        }
        return "";
    }
    /**
     * Перенаправление страници к другому узлу сайта
     * 
     * @param Array $path Путь к странице
     * @param Array $request Параметры передаваемые странице
     * @param int $status Статус передаваемый при перенаправлении
     * @param bool $isAjax Указывает на то что это Ajax запрос
     */
    public static function Redirect($path="", $request=[], $status = 302, $isAjax = false){
        header('Location: ' . \Registry::Link($path, $request), true, $status);
        if(\Registry::IsAjax() || $isAjax) header( "X-Requested-With: XMLHttpRequest", true);
        exit();
    }
    /**
     * Перенаправление на страница не найдена
     */
    public static function RedirectNotFound(){
        \Registry::Redirect(\Registry::$Request->PathNotFound,['msg'=>$_SERVER['REQUEST_URI']]);
    }
    /**
     * Выполнение произвольного метода произвольного контроллера по указанному пути
     * 
     * @param mixed $route Mаршрут к контроллеру
     * @param array $data Передаваемые методу параметры
     * @param string $prefixClass Корень пространства имен контроллера
     * @param string $prefixMethod Приставка к имени метода контроллера
     * @param bool $error Если TRUE, то запрещает подстановку NotFound, когда класс контроллера не найден (требуется для исключения зацикливания перехода по NotFound)
     * @return mixed Возвращает результат выполнения метода
     */
    public static function Controller($route, $data=[], $prefixClass=null, $prefixMethod=null, $error=false){        
        return (new Action($route, $prefixClass, $prefixMethod, $error))->Execute($data);
    }
    /**
     * Определяет послан ли запрос через Ajax
     * 
     * @return boolean
     */
    public static function IsAjax(){
        return (\Registry::$Request!=null && array_key_exists('HTTP_X_REQUESTED_WITH',\Registry::$Request->Server) && strtolower(\Registry::$Request->Server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }
    /**
     * Переводит ключь или массив ключей
     * 
     * @param mixed $key Ключь или массив ключей для перевода
     * @param string $code Трехбуквенный код языка, если не указан используется \Registry::Code
     * @return mixed Возвращает переведенную строку или массив переводов без изменения ключей массива
     */
    public static function Translate($key, $code=null){
        if(isset(\Registry::$Lng)){
            //if(!isset(\Registry::$Lng)) \Registry::$Lng = new \Config\Lng($this);
            //return \Registry::$Lng->Translate($key, $code);
        }
        return $key;
    }
    public static function LngExistsCode($code){
        return (isset(\Registry::$Lng) && \Registry::$Lng->ExistsCode($code));
    }
}