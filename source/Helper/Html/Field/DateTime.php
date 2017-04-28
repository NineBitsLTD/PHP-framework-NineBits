<?php

namespace Sys\Helper\Html\Field;

/** 
 * @uses \Sys\Helper
 * @uses \Sys\Helper\Security
 */
class DateTime implements \Sys\Helper\Html\FieldInterface {
    public static function PrintScriptAfter(){
        $lng='ru';
        if(array_key_exists("Registry", $GLOBALS) && isset($GLOBALS["Registry"]->Lng))
            $lng = $GLOBALS["Registry"]->Lng->GetCode();
        ?>
        <script>
            $(".datetimepicker").datetimepicker({
                locale: '<?=$lng?>',
                format: 'YYYY-MM-DD HH:mm:ss',
                widgetPositioning:{horizontal:'left'},
                toolbarPlacement:'bottom',
                showTodayButton:true,
                useCurrent: false,
            });
        </script>
        <?php
    }
    public static function PrintField(& $reg, $key, $fields = [], $item = [], $class="") {
        ?>        
        <div class="form-group <?=isset($class)?$class:'form-group col-ss-12 col-xs-6 col-sm-4 col-lg-3'?>">
            <label class="control-label padding_top"><?=(array_key_exists('text', $fields[$key]) && $fields[$key]['text']!="")?$fields[$key]['text']:$key?></label>   
            <div class="input-group datetimepicker">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                <input type="text" 
                       class="form-control" 
                       name="item[<?=\Sys\Helper::$Security->EscapeEncodeHtml($key)?>]"
                       value="<?=\Sys\Helper::$Security->EscapeEncodeHtml((isset($item) && \Sys\Helper::$Array->KeyIsSet($key, $item))?date('Y-m-d H:i:s',strtotime($item[$key])):date('Y-m-d H:i:s',time()))?>">
            </div>
        </div>
        <?php
    }

}