<?php

namespace Sys\Helper\Html\Field;

/** 
 * @uses \Sys\Helper
 * @uses \Sys\Helper\Security
 */
class Select implements \Sys\Helper\Html\FieldInterface {

    public static function PrintField(& $reg, $key, $fields = [], $item = [], $class="") {         
        if(!array_key_exists('list', $fields[$key]) || !is_array($fields[$key]['list'])) $fields[$key]['list']=[]; 
        ?>
        <div class="form-group <?=isset($class)?$class:'form-group col-ss-12 col-xs-6 col-sm-4 col-lg-3'?>">
            <label class="control-label padding_top"><?=(array_key_exists('text', $fields[$key]) && $fields[$key]['text']!="")?$fields[$key]['text']:$key?></label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>
                <select class="form-control" onchange="$(this).next('input').val(this.value);">
                    <?php foreach ($fields[$key]['list'] as $k => $v) { ?>
                    <option value="<?=\Sys\Helper::$Security->EscapeEncodeHtml($k)?>" <?=(\Sys\Helper::$Array->KeyIsSet($key, $item) && $item[$key]==$k)?'selected':''?>><?=$v?></option>
                    <?php } ?>
                </select>
                <input type="hidden" 
                       name="item[<?=\Sys\Helper::$Security->EscapeEncodeHtml($key)?>]" 
                       value="<?=(\Sys\Helper::$Array->KeyIsSet($key, $item))?\Sys\Helper::$Security->EscapeEncodeHtml($item[$key]):""?>">
            </div>
        </div>
        <?php
    }

}
