<?php
namespace Helper;

/**
 * Операции с HTML контентом
 * 
 * @uses \Sys\Helper\Html\Field\Number
 * @uses \Sys\Helper\Html\Field\Float
 * @uses \Sys\Helper\Html\Field\Checkbox
 * @uses \Sys\Helper\Html\Field\Select
 * @uses \Sys\Helper\Html\Field\ColumnsXls
 * @uses \Sys\Helper\Html\Field\File
 * @uses \Sys\Helper\Html\Field\FileXls
 * @uses \Sys\Helper\Html\Field\SelectText
 * @uses \Sys\Helper\Html\Field\Text
 */
class Html
{
    /**
     * Печать меню в виде перечня элементов <li> используя массив конфигурации меню
     * 
     * @param array $menu Массив конфигурации меню
     * @param string $active Ключ активного елемента
     */
    function PrintMenuItems($menu, $active=""){
        if(is_array($menu)) foreach ($menu as $key => $item) if(is_array($item) && array_key_exists('view', $item) && $item['view']){
            if(array_key_exists('divider', $item) && $item['divider']===true){ ?>
                <li class="divider"></li>
            <?php } else if(array_key_exists('items', $item)){ ?>
                <li class="dropdown <?=(in_array(strtolower($active), array_keys($item['items'])))?'open':''?> <?=(array_key_exists('class', $item)?\Sys\Helper::$Security->EscapeEncodeHtml($item['class']):'')?>" 
                    <?=(array_key_exists('title', $item)?'title="'.\Sys\Helper::$Security->EscapeEncodeHtml($item['title']).'"':'')?>>
                    <a class="dropdown-toggle" onclick="$(this).parent().toggleClass('open'); event.propagation;">
                    <?php if(array_key_exists('icon', $item)){ ?>
                        <i class="fa fa-<?=$item['icon']?>"></i>
                    <?php } ?>
                    <?php if(array_key_exists('text', $item)){ ?>
                        <span><?=$item['text']?></span>
                    <?php } ?>
                    <b class="caret"></b></a>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <?=$this->PrintMenuItems($item['items'], $active)?>
                    </ul>
                </li>
            <?php
            } else { if(array_key_exists('icon', $item) || array_key_exists('text', $item)){  ?>
                <li class="<?=(strtolower($active) == $key)?'active':''?> <?=(array_key_exists('class', $item)?\Sys\Helper::$Security->EscapeEncodeHtml($item['class']):'')?>" 
                    <?=(array_key_exists('title', $item)?'title="'.\Sys\Helper::$Security->EscapeEncodeHtml($item['title']).'"':'')?>>
                <a 
                    <?php if(array_key_exists('link', $item)){ ?>href="<?=$item['link']?>"<?php } ?>
                >
            <?php if(array_key_exists('icon', $item)){ ?>
                    <i class="fa fa-<?=$item['icon']?>"></i>
            <?php } ?>
            <?php if(array_key_exists('text', $item)){ ?>
                    <span><?=$item['text']?></span>
            <?php } ?>
                </li></a> 
            <?php
            } }
        }
    }
    function PrintButton($item, $link_postfix="", $class=""){ ?>
        <button type="<?=array_key_exists('type', $item)?$item['type']:'button'?>" 
                class="btn btn-default <?=(array_key_exists('class', $item)?\Sys\Helper::$Security->EscapeEncodeHtml($item['class']):'')?> <?=$class?>" 
                <?php if(array_key_exists('title', $item)){ ?>title="<?=$item['title']?>"<?php } ?>
                <?php if(array_key_exists('link', $item) && !array_key_exists('onclick', $item)){                     
                    $url = parse_url($item['link']);
                    if(array_key_exists('query', $url) && $url['query']!='' && $link_postfix!='') $url = $item['link']."&".$link_postfix;
                    else if((!array_key_exists('query', $url) || $url['query']=='') && $link_postfix!='') $url = $item['link']."?".$link_postfix;
                    else $url = $item['link'];
                    ?>
                onclick="window.location = '<?=$url?>';"
                <?php } else if(array_key_exists('onclick', $item)) { ?>
                onclick="<?=$item['onclick']?>"
                <?php } ?>
            >
            <?php if(array_key_exists('icon', $item)){ ?>
            <i class="fa fa-<?=$item['icon']?>"></i>
            <?php } ?>
            <?php if(array_key_exists('text', $item)){ ?>
            <span class="hidden visible-md visible-lg"><?=$item['text']?></span>
            <?php } ?>
        </button> 
        <?php
    }
    /**
     * Печать группы кнопок в виде перечня элементов <button> используя массив конфигурации меню
     * 
     * @param type $menu Массив конфигурации меню
     * @param type $link_postfix Приставка к ссылке, чаще всего строка get запроса содержащая id элемента
     */
    function PrintGroupButtons($menu, $link_postfix="", $class=""){
        foreach ($menu as $key => $item) if(!array_key_exists('view', $item) || $item['view']===true) 
            $this->PrintButton ($item, $link_postfix, $class);
    }
    /**
     * Печать HTML контента соответственно формату ячейки данных
     * 
     * @param \Sys\Registry $reg
     * @param string $key Ключь столбца для которого выполняется печать
     * @param array $fields Параметры полей
     * @param array $item Запись из базы данных
     */
    function PrintField(& $reg, $key, $fields=[], $item=[], $class=""){
        if(!\Sys\Helper::$Array->KeyIsSet($key, $fields)) return;
        if(!array_key_exists('type', $fields[$key])) $fields[$key]['type']='';
        switch($fields[$key]['type']){ 
            case 'Number': \Sys\Helper\Html\Field\Number::PrintField($reg, $key, $fields, $item, $class); break;
            case 'Float': \Sys\Helper\Html\Field\NumberFloat::PrintField($reg, $key, $fields, $item, $class); break;
            case 'Checkbox': \Sys\Helper\Html\Field\Checkbox::PrintField($reg, $key, $fields, $item, $class); break;
            case 'File': \Sys\Helper\Html\Field\File::PrintField($reg, $key, $fields, $item, $class); break;
            case 'Select': \Sys\Helper\Html\Field\Select::PrintField($reg, $key, $fields, $item, $class); break;
            case 'SelectPath': \Sys\Helper\Html\Field\SelectPath::PrintField($reg, $key, $fields, $item, $class); break;
            case 'SelectText': \Sys\Helper\Html\Field\SelectText::PrintField($reg, $key, $fields, $item, $class); break;
            case 'Text': \Sys\Helper\Html\Field\Text::PrintField($reg, $key, $fields, $item, $class); break;
            case 'StrLng': \Sys\Helper\Html\Field\StrLng::PrintField($reg, $key, $fields, $item, $class); break;
            case 'DateTime': \Sys\Helper\Html\Field\DateTime::PrintField($reg, $key, $fields, $item, $class); break;
            case 'Wysiwyg': \Sys\Helper\Html\Field\Wysiwyg::PrintField($reg, $key, $fields, $item, $class); break;
            default: \Sys\Helper\Html\Field\Str::PrintField($reg, $key, $fields, $item, $class); break;
        }
    }
    /**
     * 
     * @param \Sys\Registry $reg
     * @param type $fields
     * @param type $item
     * @param type $class
     */
    function PrintFields(& $reg, $fields, $item=[], $class=""){
        $existsAfter = [];
        foreach ($fields as $key => $value) if(\Sys\Helper::$Array->KeyIsSet('edit', $value) && boolval($value['edit'])){
            $this->PrintField($reg, $key, $fields, $item, $class);
            switch($fields[$key]['type']){ 
                case 'StrLng':
                case 'DateTime':
                case 'Wysiwyg':  
                $existsAfter[$fields[$key]['type']] = true; break;
            }
        }
        foreach ($existsAfter as $key => $value) {
            $reflectionMethod = new \ReflectionMethod("\\Sys\\Helper\\Html\\Field\\{$key}", 'PrintScriptAfter');
            $reflectionMethod->invoke(null, $reg);
        }
    }
    function PrintArrAsInputs($data, $parent_key=null){
        foreach ($data as $key => $value) {
            $pkey = isset($parent_key)?$parent_key."[{$key}]":$key;
            if(is_array($value) || is_object($value)) $this->PrintArrAsInputs ($value, $pkey);
            else echo "<input type=\"hidden\" name=\"{$pkey}\" value=\"".\Sys\Helper::$Security->EscapeEncodeHtml($value)."\">";
        }
    }
}