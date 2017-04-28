<?php

namespace Sys;
/**
 * Шаблон контроллера
 * 
 * Предназначен для наследования контроллерами, дает возможность описать базовые методы для всех контроллеров
 *  
 */
class Controller extends \Sys\Object {
    public $PageName = "";
    /**
     * Действие запустившее контроллер
     * 
     * @var \Sys\Action
     */
    public $Action = null;
    /**
     * Список скрытых параметров для передачи в post
     * 
     * @param array $data Ассоциативный одномерный масив с параметрами, где ключь - имя для input, а значение - значение для input
     */
    public static function PrintParams($data){
        foreach ($data as $key => $value) { ?>
            <input type="hidden" name="<?= $key ?>" value="<?= \Sys\Helper::$Security->EscapeEncodeHtml($value) ?>">
        <?php }
    }

    /**
     * Печатает HTML контент
     * 
     * @param array $data
     */
    public function methodIndex($data=[]){
        echo $this->Render($data);
    }
    
    /**
     * Формирование шаблона с генерацией переменных для вставки шапки и футера страницы
     * если запрос был отправлен, или помечен как ajax, шапка и футер не добавляются
     * 
     * @param Array $data Параметры передаваемые шаблону
     * @return string Возвращает HTML контент
     */
    protected function Render($data=[], $postfix=""){
        if(!array_key_exists('ajax', $data)) $data['ajax'] = $this->Reg->IsAjax();
        if(!array_key_exists('base', $data)) $data['base'] = $this->Reg->Link();
        if(!array_key_exists('theme', $data)) $data['theme'] = $this->Reg->View->Theme;
        if(!array_key_exists('page_name', $data)) $data['page_name'] = $this->PageName;
        if(!array_key_exists('is_logged', $data)) $data['is_logged'] = $this->Reg->Session->IsLogged();
        if(!array_key_exists('breadcrumbs', $data)) $data['breadcrumbs'] = $this->Breadcrumbs($this->Action->GetRoute());
        if($data['ajax']!==true){
            if(!array_key_exists('header_menu', $data)) $data['header_menu'] = $this->Reg->Controller('header_menu', $data);
            if(!array_key_exists('header', $data)) $data['header'] = $this->Reg->View->Render('header', $data);            
            if(!array_key_exists('footer', $data)) $data['footer'] = $this->Reg->View->Render('footer', $data);
        }
        return $this->RenderModify($postfix, $data);
    }
    /**
     * Формирование чистого шаблона страницы c приставкой $postfix в названии
     * 
     * @param string $postfix Приставка кимени шаблона
     * @param Array $data Параметры передаваемые шаблону
     * @return string Возвращает HTML контент
     */
    protected function RenderModify($postfix="", $data=[]){
        $data['theme'] = $this->Reg->View->Theme;
        $path = $this->Action->GetView();
        if(count($path)>0) $path[count($path)-1] .= $postfix;
        return $this->Reg->View->Render($path, $data, $path_view=null);
    }

    /**
     * Проверка на авторизацию, если не проведена перенаправляет на контроллер $pathLogin
     */
    protected function CheckLogin(){
        if(!$this->Reg->Session->IsLogged()) {
            $this->Reg->Redirect($this->Reg->Request->PathStart);
        }
        if($this->Reg->Session->User->Status<5 &&
                $this->PageName!='Guest' &&
                $this->PageName!='Payment' &&
                $this->PageName!='Profile'
            ) {
            $this->Reg->RedirectNotFound();
        }
    }
    /**
     * Адресная строка (след из хлебных крошек)
     * 
     * @param array $route Путь к странице
     * @param array $dictionary Словарь переводов ключей пути
     * @return array Вложенный массив содержащий массивы ссылок и названий
     */
    protected function Breadcrumbs($route) {
        $route = \Sys\Helper::$Route->FromClass($route);
        $route = \Sys\Helper::$Route->ToClass($route);
        $breadcrumbs = ['Home'=>[
            'href'=>$this->Reg->Link('Home'),
            'text'=>$this->Reg->Translate('HeaderMenu_Home')
        ]];
        $path = '';
        $text = '';
        $sep = '';
        $i = 0;
        foreach ($route as $value) {
            $path .= $sep.$value;
            $text .= $value;
            $breadcrumbs[$value]=[
                'text'=>$this->Reg->Translate("HeaderMenu_".$text)                
            ];
            if($i<(count($route)-1)){                
                $breadcrumbs[$value]['href']=$this->Reg->Link($path);
            }
            $sep = "/";
            $i++;
        }
        return $breadcrumbs;
    }
    /**
     * 
     * @param type $filename
     * @param type $id
     * @param type $key
     * @return type
     */
    protected function MoveUploaded($filename, $id){
        $dir_file = $this->Reg->Path->Tmp;
        if (!is_file($filename)) return;

        $new_file = $dir_file.DIRECTORY_SEPARATOR.$this->Reg->Session->GetUserId().
                DIRECTORY_SEPARATOR . $id . ".xls";
        if (!is_dir($dir_file.DIRECTORY_SEPARATOR.$this->Reg->Session->GetUserId())) {
            @mkdir($dir_file.DIRECTORY_SEPARATOR.$this->Reg->Session->GetUserId(), 0777);
        }
        $result = move_uploaded_file($filename, $new_file);
        if(!array_key_exists('msg', $this->Reg->Session->Data)) $this->Reg->Session->Data['msg']=[];
        if($result){
            if(!array_key_exists('success', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['success']="";
            $this->Reg->Session->Data['msg']['success'] .= "Файл успешно загружен. ";
        } else {
            if(!array_key_exists('error', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['error']="";
            $this->Reg->Session->Data['msg']['error'] .= "Не удалось загрузить файл. ";
        }
    }
    /**
     * Добавляет пункт меню в массив конфигурации меню
     * 
     * @param array $arr Массив конфигурации меню
     * @param string $key Ключь пункта
     * @param string $key_title Ключь к переводу названия пункта
     * @param boolean $view Следует ли отобразить меню
     * @param string $icon Иконка пункта меню fa-
     * @param array $items Пункты подменю
     * @param string $class Класс пункта
     * @param string $type Тип пункта (если кнопка)
     * @param string $link Ссылка на пункт
     * @param boolean $view_text Формировать текст автоматически
     * @param boolean $viev_link Формировать ссылку автоматически
     */
    protected function AddItem(&$arr, $key, $key_title, $view=true, $icon='file-text-o', $items=null, $class=null, $type=null, $link=null, $view_text=true, $view_link=true, $onclick=null){
        if(!isset($class)) $class = "";
        $arr[$key] = [            
            'class'=>$class,
            'view'=>$view && (isset($this->Reg->Session->User->Role)?$this->Reg->Session->User->Role->Check($key):true),
            'icon'=>$icon,
            'title' => $this->Reg->Translate($key_title)
        ];
        if($view_link) $arr[$key]['link'] = isset($link)?$link:$this->Reg->Link($key);
        if($view_text) $arr[$key]['text'] = $this->Reg->Translate($key_title);
        if(isset($type)) $arr[$key]['type'] = $type;
        if(isset($onclick)) $arr[$key]['onclick'] = $onclick;
        if(isset($items) && is_array($items)) $arr[$key]['items'] = $items;
    }
    protected function GetLngMenu($class, $logout = false){
        if(isset($this->Reg->Lng)){
            $list = '';
            $current = '';
            $items = $this->Reg->Lng->GetCodes();
            foreach ($items as $key => $item) {
                if($this->Reg->Lng->GetCode()==$item['code']){
                    $current = '<button class="flag flag-'.$item['flag'].' dropdown-toggle" type="button" data-toggle="dropdown" title="'.\Sys\Helper::$Security->EscapeEncodeHtml($item['title']).'"><span class="caret"></span></button>';
                } else {
                    if($logout) $list .= '<li onclick="window.location=\''.$this->Reg->Link($item['code']).'\';"><a><i class="flag flag-'.$item['flag'].'"></i> '.\Sys\Helper::$Security->EscapeEncodeHtml($item['title']).'</a></li>';
                    else $list .= '<li onclick="sys.ajax({\'lng\':\''.$item['code'].'\'},\'\',function(){window.location.reload();});"><a><i class="flag flag-'.$item['flag'].'"></i> '.\Sys\Helper::$Security->EscapeEncodeHtml($item['title']).'</a></li>';
                }
            }
            return '<div class="dropdown '.$class.'">'.$current.'<ul class="dropdown-menu">'.$list.'</ul></div>';
        }
        return "";
    }
}

