<?php

namespace Sys\Helper\Html\Field;

/** 
 * @uses \Sys\Helper
 * @uses \Sys\Helper\Security
 */
class Number implements \Sys\Helper\Html\FieldInterface {

    public static function PrintField(& $reg, $key, $fields = [], $item = [], $class="") {
        ?>
        <div class="form-group <?=isset($class)?$class:'form-group col-ss-12 col-xs-6 col-sm-4 col-lg-3'?>">
            <label class="control-label padding_top"><?=(array_key_exists('text', $fields[$key]) && $fields[$key]['text']!="")?$fields[$key]['text']:$key?></label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-list-ol"></i></span>
                <input type="number" 
                       class="form-control" 
                       name="item[<?=\Sys\Helper::$Security->EscapeEncodeHtml($key)?>]"
                       value="<?=(\Sys\Helper::$Array->KeyIsSet($key, $item))?\Sys\Helper::$Security->EscapeEncodeHtml($item[$key]):""?>">
            </div>
        </div>
        <?php
    }

}
