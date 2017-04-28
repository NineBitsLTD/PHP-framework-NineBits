<?php

namespace Sys\Helper\Html\Field;

/** 
 * @uses \Sys\Helper
 * @uses \Sys\Helper\Security
 */
class StrLng implements \Sys\Helper\Html\FieldInterface {
    public static function PrintScriptAfter($reg){
        $languages = $reg->Lng->GetCodes(); 
        $lng = $reg->Lng->GetByCode();
        ?>
        <script>
            if(sys == undefined) sys={};
            if(sys.fields == undefined) sys.fields={};
            sys.fields.StrLng={
                'Select':function(code, title, hash){
                    var item = $('#tab_'+code+'_'+title+'_'+hash); 
                    var tab = $('#'+code+'_'+title+'_'+hash);
                    if(item.length>0 && tab.length>0) {
                        item.addClass('active'); item.siblings().removeClass('active');
                        tab.addClass('active'); tab.siblings().removeClass('active');
                        return true;
                    } else return false;
                },
                'SelectFirst':function(title, hash){
                    var item = $('#tabs_'+title+'_'+hash+' .field-strlng-menu>li.tab').first(); 
                    var tab = $('#tabs_'+title+'_'+hash+' .tab-content .tab-pane').first();
                    if(item.length>0 && tab.length>0) {
                        item.addClass('active'); item.siblings().removeClass('active');
                        tab.addClass('active'); tab.siblings().removeClass('active');
                        return true;
                    } else return false;
                },
                'Add':function(code, title, hash, flag, language){
                    var item = $('#tab_'+code+'_'+title+'_'+hash); 
                    var tab = $('#'+code+'_'+title+'_'+hash);
                    if(!sys.fields.StrLng.Select(code, title, hash)) {
                        var tabs = $('#tabs_'+title+'_'+hash+' .tab-content');
                        var menu = $('#tabs_'+title+'_'+hash+' .field-strlng-menu');
                        tab ='<div id="'+code+'_'+title+'_'+hash+'" class="tab-pane">\
                            <div class="input-group">\n\
                                <div class="input-group-btn">\n\
                                    <button class="btn btn-danger" onclick="$(\'#tab_'+code+'_'+title+'_'+hash+'\').remove(); $(\'#'+code+'_'+title+'_'+hash+'\').remove(); sys.fields.StrLng.SelectFirst(\''+title+'\', \''+hash+'\');" title="<?=$reg->Translate('BtnDeleteField')?>">\n\
                                        <i class="fa fa-remove"></i>\n\
                                    </button>\n\
                                </div>\n\
                                <input type="text"\n\
                                       class="form-control"\n\
                                       name="item['+title+']['+code+']"\n\
                                       value="">\n\
                            </div>\n\
                        </div>';
                        item = '<li id ="tab_'+code+'_'+title+'_'+hash+'" class="tab"><a data-toggle="pill" href="#'+code+'_'+title+'_'+hash+'" title="'+language+'"><i class="flag flag-'+flag+'"></i></a></li>';
                        $(tabs).append(tab);
                        $(menu).append(item);
                        sys.fields.StrLng.Select(code, title, hash);
                    }
                }
            }
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
        $languages = $reg->Lng->GetCodes(); 
        $lng = $reg->Lng->GetByCode();
        $hash = \Sys\Helper::$Security->TokenCreate(16);
        ?>        
        <div id="tabs_<?=$key?>_<?=$hash?>" class="form-group <?=isset($class)?$class:'form-group col-ss-12 col-xs-6 col-sm-4 col-lg-3'?>">
            <label class="control-label padding_top"><?=(array_key_exists('text', $fields[$key]) && $fields[$key]['text']!="")?$fields[$key]['text']:$key?></label>
            <ul class="nav nav-pills field-strlng-menu">
                <li class="dropdown">
                    <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-plus"></i>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <?php  foreach ($languages as $value) { ?>
                        <li onclick="sys.fields.StrLng.Add('<?=$value['code']?>', '<?=$key?>', '<?=$hash?>', '<?=$value['flag']?>', '<?=$value['title']?>');">
                            <a><i class="flag flag-<?=$value['flag']?>"></i> <?=$value['title']?></a>
                        </li>
                        <?php } ?>
                    </ul>
                </li>
                <?php $first=true; if(array_key_exists($key, $item) && is_array($item[$key])) foreach($item[$key] as $lang => $value) { 
                    $lang = $reg->Lng->GetByCode($lang);  
                    if(count($lang)>0) { ?>
                <li id ="tab_<?=$lang['code']?>_<?=$key?>_<?=$hash?>" class="tab <?=$first?'active':''?>"><a data-toggle="pill" href="#<?=$lang['code']?>_<?=$key?>_<?=$hash?>" title="<?=$lang['title']?>"><i class="flag flag-<?=$lang['flag']?>"></i></a></li>
                <?php $first=false; } } ?>
            </ul>
            <div class="tab-content">
                <?php $first=true; if(array_key_exists($key, $item) && is_array($item[$key])) foreach($item[$key] as $lang => $value) { 
                    $lang = $reg->Lng->GetByCode($lang); 
                    if(count($lang)>0) { ?>
                <div id="<?=$lang['code']?>_<?=$key?>_<?=$hash?>" class="tab-pane <?=$first?'active':''?>">
                    <div class="input-group">
                        <div class="input-group-btn">t('<?=$lang['code']?>', '<?=$key?>', '<?=$hash?>');
                            <button class="btn btn-danger" onclick="$('#tab_<?=$lang['code']?>_<?=$key?>_<?=$hash?>').remove(); $('#<?=$lang['code']?>_<?=$key?>_<?=$hash?>').remove(); sys.fields.StrLng.SelectFirst('<?=$key?>', '<?=$hash?>');" title="<?=$reg->Translate('BtnDeleteField')?>">
                                <i class="fa fa-remove"></i>
                            </button>
                        </div>  
                        <input type="text" 
                               class="form-control" 
                               name="item[<?=\Sys\Helper::$Security->EscapeEncodeHtml($key)?>][<?=$lang['code']?>]"
                               value="<?=(\Sys\Helper::$Array->KeyIsSet($key, $item) &&  \Sys\Helper::$Array->KeyIsSet($lang['code'], $item[$key]))?\Sys\Helper::$Security->EscapeEncodeHtml($item[$key][$lang['code']]):""?>">                  
                    </div>
                </div>
                <?php $first=false; } } ?>
            </div>
        </div>
        <?php
    }

}
