<?php

namespace Sys\Helper\Html\Field;

/** 
 * @uses \Sys\Helper
 * @uses \Sys\Helper\Security
 */
class SelectPath implements \Sys\Helper\Html\FieldInterface {

    public static function PrintField(& $reg, $key, $fields = [], $item = [], $class="") {         
        if(!array_key_exists('list', $fields[$key]) || !is_array($fields[$key]['list'])) $fields[$key]['list']=[]; 
        if(!array_key_exists('path', $fields[$key]) || !is_array($fields[$key]['path'])) $fields[$key]['path']=[]; 
        $id=0; if(array_key_exists('id', $item)) $id=$item['id'];
        ?>
        <div class="form-group <?=isset($class)?$class:'form-group col-ss-12 col-xs-6 col-sm-4 col-lg-3'?>">
            <label class="control-label padding_top"><?=(array_key_exists('text', $fields[$key]) && $fields[$key]['text']!="")?$fields[$key]['text']:$key?></label>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-chevron-down"></i></span>
                <select class="form-control select-path"  onchange="$(this).next('input').val(this.value);">
                    <?php foreach ($fields[$key]['paths'] as $k => $path) if(array_key_exists($k, $fields[$key]['list'])){ ?>
                    <?php 
                        $v = $fields[$key]['list'][$k];
                        $path = explode(',', $path); 
                        $tab = "";
                        $sep = "";
                        foreach ($path as $value) {
                            $tab .= $sep;
                            $sep = "...";
                        }
                    ?>
                    <option path="<?=implode(',',$path)?>" value="<?=\Sys\Helper::$Security->EscapeEncodeHtml($k)?>" 
                            <?=(\Sys\Helper::$Array->KeyIsSet($key, $item) && $item[$key]==$k)?'selected':''?> 
                            <?=((int)$id>0 && (in_array($id, $path))?'disabled':'')?>><?=$tab?><?=$v?></option>
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
