<?php    

    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', 1);    
    
    /**
     *  Нарушение последовательности выполняемых операций, может привести к ошибкам.
     */
    require_once('source/Registry.php');
    require_once('source/Core/Autoloader.php');
    \Registry::$Autoloader = new \Core\Autoloader([''=>'source/'], "source/Plugins");
    \Helper::$Number = new \Helper\Number();
    \Helper::$String = new \Helper\Str();
    \Helper::$Security = new \Helper\Security();
    \Helper::$File = new \Helper\File();
    \Registry::$Path = new \Core\Path();
    \Registry::$View = new \Core\View('Default');
    \Registry::$Request = new \Core\Request('home','home','not_found');
    //$Registry->DB = new \Sys\DataBase($Registry, new \Sys\DataBase\MySql(new \Sys\Config\DataBase($Registry)));
    //$Registry->DB->Connect();
    //$Registry->Session = new \Sys\Session($Registry);
    //$Registry->Session->Start();
    //$Registry->Lng = new \Sys\Config\Lng($Registry);
    //$Registry->Mail = new \Sys\Config\Mailer($Registry);
    
    \Registry::Dispatch();
    var_dump(\Registry::$Data);
?>

            
 
    