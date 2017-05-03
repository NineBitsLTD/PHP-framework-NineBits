<?php

namespace Sys\Helper\Html\Field;

/** 
 * @uses \Sys\Helper
 * @uses \Sys\Helper\Security
 */
class Checkbox implements \Sys\Helper\Html\FieldInterface {

    public static function PrintField(& $reg, $key, $fields = [], $item = [], $class="") {
        ?>    
        <div class="<?=isset($class)?$class:'form-group col-ss-12 col-xs-6 col-sm-4 col-lg-3'?>">
            <label class="control-label padding_top"><?=(array_key_exists('text', $fields[$key]) && $fields[$key]['text']!="")?$fields[$key]['text']:$key?></label>        
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-check-square-o"></i></span>
                <select class="form-control" 
                       name="item[<?=\Sys\Helper::$Security->EscapeEncodeHtml($key)?>]">
                    <option value="0" <?=\Sys\Helper::$Array->KeyIsSet($key, $item) && boolval($item[$key])?'':'selected'?>>Нет</option>
                    <option value="1" <?=\Sys\Helper::$Array->KeyIsSet($key, $item) && boolval($item[$key])?'selected':''?>>Да</option>
                </select>
            </div>
        </div>          
        <?php
    }

}
