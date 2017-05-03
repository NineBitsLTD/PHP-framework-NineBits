<?php

namespace Sys\Helper\Html\Field;

/** 
 * @uses \Sys\Helper
 * @uses \Sys\Helper\Security
 */
class Wysiwyg implements \Sys\Helper\Html\FieldInterface {
    public static function PrintScriptAfter($reg){
        $lng='ru';
        if(array_key_exists("Registry", $GLOBALS) && isset($GLOBALS["Registry"]->Lng))
            $lng = $GLOBALS["Registry"]->Lng->GetCode();
        ?>
        <script>
            $(".summernote").summernote({
            });
        </script>
        <?php
    }
    /**
     * 
     * @param \Sys\Registry $reg
     * @param type $key
     * @param type $fields
     * @param type $item
     * @param type $class
     */
    public static function PrintField(& $reg, $key, $fields = [], $item = [], $class="") {
        ?>        
        <div class="form-group <?=isset($class)?$class:'form-group col-ss-12'?>">
            <label class="control-label padding_top"><?=(array_key_exists('text', $fields[$key]) && $fields[$key]['text']!="")?$fields[$key]['text']:$key?></label>   
            <textarea type="text" 
                       class="summernote" 
                       name="item[<?=\Sys\Helper::$Security->EscapeEncodeHtml($key)?>]"><?=(isset($item) && \Sys\Helper::$Array->KeyIsSet($key, $item))?\Sys\Helper::$Security->EscapeEncodeHtml($item[$key]):""?></textarea>
        </div>
        <?php
    }

}
