<?php

namespace Sys\Helper\Html\Field;

/** 
 * @uses \Sys\Helper
 * @uses \Sys\Helper\Security
 */
class Text implements \Sys\Helper\Html\FieldInterface {
    public static function PrintField(& $reg, $key, $fields = [], $item = [], $class="") {
        ?>        
        <div class="form-group <?=isset($class)?$class:'form-group col-ss-12'?>">
            <label class="control-label padding_top"><?=(array_key_exists('text', $fields[$key]) && $fields[$key]['text']!="")?$fields[$key]['text']:$key?></label>   
            <textarea  type="text" 
                       name="item[<?=\Sys\Helper::$Security->EscapeEncodeHtml($key)?>]"
                       class="form-control"><?=(isset($item) && \Sys\Helper::$Array->KeyIsSet($key, $item))?\Sys\Helper::$Security->EscapeEncodeHtml($item[$key]):""?></textarea>
        </div>
        <?php
    }

}
