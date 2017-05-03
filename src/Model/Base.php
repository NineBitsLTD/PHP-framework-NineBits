<?php

namespace Model;

class Base extends \Core\Object{
    /**
     *
     * @var array
     */
    public $LngColumns = [];
    public $Languages = [];
    
    /**
     * Название таблицы
     * 
     * @var string
     */
    protected $TableName = "";
    /**
     *
     * @var type 
     */
    protected $Fields = null;
    /**
     * 
     * @return boolean
     */
    protected function IsMultiLng(){
        return (count($this->LngColumns)>0 && $this->Reg!=null && $this->Reg->Lng!=null && $this->Reg->Lng!=null);
    }
    
    public function __construct(&$registry, $table_name=null) {
        parent::__construct($registry);
        if(isset($table_name) && $table_name!="") $this->TableName=$table_name;
        if($this->IsMultiLng()) $this->Languages = \Sys\Helper::$Array->GetColumnMultiarray($this->Reg->Lng->GetCodes(), 'id', 'code');
    }

    /**
     * Перечень полей таблицы и их типы
     * 
     * @return array
     */
    public function GetFields($id=null){
        if($this->Fields==null) $this->Fields = $this->Reg->DB->Query("SELECT `COLUMN_NAME` as `name`, `DATA_TYPE` as `type`, `COLUMN_DEFAULT` as `default`
            FROM `information_schema`.`COLUMNS`
            WHERE `TABLE_NAME` = '".$this->GetTableName()."' AND `TABLE_SCHEMA` = '{$this->Reg->DB->Provider->DBName}'")->Rows;
        return $this->Fields;
    }
    /**
     * Существует ли поле $name в списке $fields
     * 
     * @param type $fields
     * @param type $name
     * @return boolean
     */
    public function FieldExists($name, $fields){
        $val = \Sys\Helper::$Array->FindByField($fields, $name, 'name');
        return (isset($val)===true);
    }
    /**
     * Существует ли таблица $name в данной базе
     * 
     * @param type $name Имя таблицы
     * @return type
     */
    public function TableExists($name){
        $result = $this->Reg->DB->Query("SELECT * FROM `information_schema`.`TABLES` WHERE `TABLE_NAME` = '{$name}' AND `TABLE_SCHEMA` = '{$this->Reg->DB->Provider->DBName}'");
        return ($result->Count==1);
    }

    /**
     * Задать имя таблицы
     * 
     * @param string $name
     */
    public function SetTableName($name){
        $this->TableName = $name;
    }
    /**
     * Получить имя таблицы
     * 
     * @return string
     */
    public function GetTableName(){
        return $this->Reg->DB->Provider->Prefix.$this->TableName;
    }
    /**
     * Получить запись по id
     * 
     * @param type $id Значение колонки $id для отбора записи
     * @param string $where Дополнительное условие отбора
     * @return \Sys\DataBase\ActiveRecord
     */
    public function GetById($id, $where="", $offset=0, $limit=1, $filter=null){   
        $records = $this->Reg->DB->Query("SELECT * FROM `".$this->GetTableName()."` WHERE `id`=".(int)$id.$this->Where($where, false, $filter).$this->Limit($offset, $limit));
        if($records->Count>0 && $this->IsMultiLng()){
            foreach ($records->Rows as $key => $row) {
                $records->Rows[$key] = $this->TranslateRow($row);
            }
            $records->Row = $records->Rows[0];
        }
        return $records;
    }
    /**
     * Список всех записей
     *  
     * @param string $where Дополнительное условие отбора
     * @return \Sys\DataBase\ActiveRecord
     */
    public function GetAll($where="", $offset=0, $limit=0, $sort=null, $filter=null){
        $sql = "SELECT * FROM `".$this->GetTableName()."`".$this->Where($where, true, $filter).$this->Sort($sort).$this->Limit($offset, $limit);
        //print_r([$where, $this->Where($where, true, $filter), $offset, $limit, $sort, $sql]); 
        //exit();
        $records = $this->Reg->DB->Query($sql);
        if($records->Count>0 && $this->IsMultiLng()){
            foreach ($records->Rows as $key => $row) {
                $records->Rows[$key] = $this->TranslateRow($row);
            }
            $records->Row = $records->Rows[0];
        }
        return $records;
    }
    /**
     * Количество записей соответствующих указанному условию
     * 
     * @param string $where
     * @param string $nameId
     * @return integer
     */
    public function GetTotal($where="", $nameId="id", $filter=null){
        $sql = "SELECT count(`{$nameId}`) as 'count' FROM `".$this->GetTableName()."`".$this->Where($where, true, $filter);
        //print_r([$where, $this->Where($where, true, $filter), $offset, $limit, $sort, $sql]); 
        //exit();
        $records = $this->Reg->DB->Query($sql);
        return (array_key_exists('count', $records->Row)?(int)$records->Row['count']:0);
    }
    /**
     * Список всех записей в виде одномерного массива, 
     * где $nameId - имя колонки ключа,
     * а $nameValue - имя колонки значений
     * 
     * @param string $where Дополнительное условие отбора
     * @param string $nameValue Имя колонки со значением, по умолчанию title
     * @param string $nameId Имя колонки с ключами, по умолчанию id
     * @return array
     */
    public function GetList($where="", $nameValue="title", $nameId="id", $offset=0, $limit=0, $sort=null, $filter=null){
        $result=[];
        $records = $this->Reg->DB->Query("SELECT max(`{$nameId}`) as `$nameId`, `$nameValue` FROM `".$this->GetTableName()."` ".$this->Where($where, true, $filter)." GROUP BY `{$nameId}`, `{$nameValue}`".$this->Sort($sort).$this->Limit($offset, $limit));
        foreach ($records->Rows as $key => $value) if(is_array($value) && array_key_exists($nameId, $value) && array_key_exists($nameValue, $value)) {
            $result[$value[$nameId]] = $value[$nameValue];
        }
        return $result;
    }
    
    /**
     * Сохранение записи в базе данных
     * 
     * @param string $data Массив сохраняемых данных
     * @return string Идентификатор записи
     */
    public function Save($data, $where=""){
        //print_r($data);
        $id = "";
        if(isset($this->Reg->Session) && !array_key_exists('msg', $this->Reg->Session->Data)) $this->Reg->Session->Data['msg']=[];
        if(isset($data) && \Sys\Helper::$Array->KeyIsSet('item', $data) && is_array($data['item'])){
            $is_insert = true;
            if(array_key_exists('id', $data['item']) && (int)$data['item']['id']>0 && $this->GetById($data['item']['id'], $where)->Count>0) $is_insert = false;
            $fields = $this->GetFields();            
            $sql="";
            //print_r([$is_insert]); exit();
            if($is_insert){
                $sep="";
                $head="";
                $values="";
                foreach($fields as $field){
                    if($field['name']!='id' && array_key_exists($field['name'], $data['item'])){
                        $head .= $sep . "`{$field['name']}`";
                        if(is_null($data['item'][$field['name']])) $values .= $sep . "NULL ";
                        else if(!is_array($data['item'][$field['name']])) $values .= $sep ."'" . $this->Escape((string)$data['item'][$field['name']]) . "' ";
                        else if($this->IsMultiLng() && in_array($field['name'], $this->LngColumns)){
                            $values .= $sep ."'" . implode(',',  array_keys($data['item'][$field['name']])) . "' ";
                        } else $values .= $sep ."'' ";
                        $sep = ",";
                    }
                }                
                $sql="INSERT INTO `".$this->GetTableName()."` ({$head}) VALUES({$values})";
                //print_r($sql); exit();
                $this->Reg->DB->Query($sql);
                $id = $this->Reg->DB->GetLastId();
                foreach($fields as $field) 
                if($field['name']!='id' && array_key_exists($field['name'], $data['item']) && 
                    $this->IsMultiLng() && in_array($field['name'], $this->LngColumns)){
                    $this->TranslateSave($field['name'], $id, $data['item'][$field['name']]);
                }
            }else{
                $sep="";
                $values="";
                $id = (int)$data['item']['id'];
                foreach($fields as $field){
                    if($field['name']!='id' && array_key_exists($field['name'], $data['item'])){
                        if(is_null($data['item'][$field['name']])) $values .= $sep . "NULL ";
                        else if(!is_array($data['item'][$field['name']])) $values .= $sep ."`{$field['name']}` = '" . $this->Escape((string)$data['item'][$field['name']]) . "' ";
                        else if($this->IsMultiLng() && in_array($field['name'], $this->LngColumns)){
                            $this->TranslateSave($field['name'], $id, $data['item'][$field['name']]);
                            $values .= $sep ."`{$field['name']}` = '" . implode(',',array_keys($data['item'][$field['name']])) . "' ";
                        } else $values .= $sep ."`{$field['name']}` = '' ";
                        $sep = ",";
                    } else if($field['name']=='updated_at' && $field['type']=='timestamp'){
                        $values .= $sep ."`updated_at` = CURRENT_TIMESTAMP ";
                        $sep = ",";
                    }
                }
                $sql = "UPDATE `".$this->GetTableName()."` SET {$values} WHERE `id` = " . $id;
                //print_r($sql); exit();
                $this->Reg->DB->Query($sql);
            }
        }
        if(isset($this->Reg->Session)){
            if((int)$id>0) {
                if(!array_key_exists('success', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['success']="";
                $this->Reg->Session->Data['msg']['success'] .= "Данные успешно сохранены. ";
            } else {
                if(!array_key_exists('error', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['error']="";
                $this->Reg->Session->Data['msg']['error'] .= "Не удалось сохранить данные. ";
            }
        }
        return $id;
    }
    /**
     * Удаление записи илизапись из базы
     * 
     * @param mixed $data Должен содержать елемент select содержащий список индексов или $this->Reg->Request->Get должен содержать индекс записи
     * @param type $where Дополнительное условие отбора
     */
    public function Delete($data, $where = null){
        if(!array_key_exists('msg', $this->Reg->Session->Data)) $this->Reg->Session->Data['msg']=[];
        if(\Sys\Helper::$Array->KeyIsSet('selected', $data) && is_array($data['selected'])){
            foreach ($data['selected'] as $key => $value) {
                if($value!='on') unset($data['selected'][$key]);
            }
        }
        if(\Sys\Helper::$Array->KeyIsSet('selected', $data) && is_array($data['selected']) && count($data['selected'])>0){
            $ids="";
            $sep="";
            foreach ($data['selected'] as $key => $value) {
                if((int)$key>0 && $value=='on'){
                    $ids .= $sep . "`id` = ".(int)$key;
                    $sep = " OR ";
                }
            }
            if(isset($where)) $ids .= " AND ({$where})";
            $sql = "DELETE FROM `".$this->GetTableName()."` ".$this->Where($ids);
            $this->Reg->DB->Query($sql);
            if(!array_key_exists('success', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['success']="";
            if(isset($this->Reg->Session)) $this->Reg->Session->Data['msg']['success'] .= "Выбранные записи успешно удалены. ";
            return;
        } else if(\Sys\Helper::$Array->KeyIsSet('id', $this->Reg->Request->Get) && (int)$this->Reg->Request->Get['id']>0){
            $ids = "`id`=".(int)$this->Reg->Request->Get['id'];
            if(isset($where)) $ids .= " AND ({$where})";
            $sql = "DELETE FROM `".$this->GetTableName()."` ".$this->Where($ids);
            $this->Reg->DB->Query($sql);
            if(!array_key_exists('success', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['success']="";
            if(isset($this->Reg->Session)) $this->Reg->Session->Data['msg']['success'] .= "Запись успешно удалена. ";
            return;
        }
        if(!array_key_exists('error', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['error']="";
        if(isset($this->Reg->Session)) $this->Reg->Session->Data['msg']['error'] .= "Не найдены записи для удаления. ";
    }
    /**
     * 
     * @param type $data
     * @param type $where Дополнительное условие отбора
     * @return int
     */
    public function SetStatus($data, $where = null){
        $id = "";
        if(isset($this->Reg->Session) && !array_key_exists('msg', $this->Reg->Session->Data)) $this->Reg->Session->Data['msg']=[];
        $sql="";
        if(isset($data) && is_array($data) && array_key_exists('id', $data) && (int)$data['id']>0 && $this->GetById((int)$data['id'], $where)->Count>0){
            $val=(array_key_exists('v', $data) && (int)$data['v']=='on');
            if(isset($where)) $where = "`id` = " . (int)$data['id'] . " AND ({$where})";
            else $where = "`id` = " . (int)$data['id'];
            $sql = "UPDATE `".$this->GetTableName()."` SET `status`=".($val?1:0).$this->Where($where);
            $this->Reg->DB->Query($sql);
            $id = $data['id'];
        } else $id=0;
        if(isset($this->Reg->Session)){
            if((int)$id>0) {
                if(!array_key_exists('success', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['success']="";
                $this->Reg->Session->Data['msg']['success'] .= "Статус успешно изменен. ";
            } else {
                if(!array_key_exists('error', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['error']="";
                $this->Reg->Session->Data['msg']['error'] .= "Не удалось изминить статус. ";
            }
        }
        return $id;
    }
    /**
     * 
     * @param type $data
     * @param type $where
     */
    public function Copy($data, $names=['title'], $name_prefix=null, $where = null){
        if(!array_key_exists('msg', $this->Reg->Session->Data)) $this->Reg->Session->Data['msg']=[];
        if(is_string($names)) $names = [$names];
        if(!is_array($names)) $names = ['title'];
        if(\Sys\Helper::$Array->KeyIsSet('selected', $data) && is_array($data['selected'])){
            foreach ($data['selected'] as $key => $value) if($this->GetById((int)$key, $where)->Count>0){
                if($value!='on') unset($data['selected'][$key]);
            }
        }
        if(\Sys\Helper::$Array->KeyIsSet('selected', $data) && is_array($data['selected']) && count($data['selected'])>0){
            $i = 0;
            foreach ($data['selected'] as $key => $value) if((int)$key>0 && $this->GetById((int)$key, $where)->Count>0){
                $this->_copy((int)$key, $names, $name_prefix, $where);
                $i++;
            }
            if(isset($this->Reg->Session)){
                if($i>=count($data['selected'])) {
                    if(!array_key_exists('success', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['success']="";
                    $this->Reg->Session->Data['msg']['success'] .= "Записи успешно скопированы. ";
                } else {
                    if(!array_key_exists('error', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['error']="";
                    $this->Reg->Session->Data['msg']['error'] .= "Некоторые записи не удалось скопировать. ";
                }
            }
            return;
        } else if(\Sys\Helper::$Array->KeyIsSet('id', $this->Reg->Request->Get) && (int)$this->Reg->Request->Get['id']>0 && $this->GetById((int)$this->Reg->Request->Get['id'], $where)->Count>0){
            $this->_copy((int)$this->Reg->Request->Get['id'], $names, $name_prefix, $where);
            if(isset($this->Reg->Session)){
                if(!array_key_exists('success', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['success']="";
                $this->Reg->Session->Data['msg']['success'] .= "Запись успешно скопирована. ";
            }
            return;
        }
        if(isset($this->Reg->Session)){
            if(!array_key_exists('error', $this->Reg->Session->Data['msg'])) $this->Reg->Session->Data['msg']['error']="";
            $this->Reg->Session->Data['msg']['error'] .= "Не удалось скопировать выбранные записи. ";
        }
        return $id;
    }    

    /**
     * Экранирование данных
     * 
     * @param string $value
     * @return string
     */
    public function Escape($value){
        return $this->Reg->DB->Escape($value);
    }
    
    /**
     * 
     * @param string $where
     * @return string
     */
    protected function Where($where, $first=true, $filter=null){
        if($where!=null && $where!="") $where = ($first?" WHERE (":" AND (").(string)$where.") ";
        return $this->Filter($where, $first, $filter);
    }
    /**
     * 
     * @param int $offset
     * @param int $limit
     * @return string
     */
    protected function Limit($offset, $limit){
        $offset = (int)$offset;
        $limit = (int)$limit;
        if($offset<1) $offset=0;
        if($limit<1) $limit = 20;
        return " LIMIT {$offset}, {$limit} ";
    }
    /**
     * 
     * 
     * @param string $sort
     * @return type
     */
    protected function Sort($sort = null){
        $result = "";
        $sep = "";    
        if(is_array($sort)) foreach ($sort as $key => $value) {
            if($this->FieldExists($key,$this->GetFields()) && (mb_strtoupper($value)=='ASC' || mb_strtoupper($value)=='DESC')){
                $result .= $sep."`{$key}` ". mb_strtoupper($value);
                $sep=', ';
            }
        }
        if($this->FieldExists('sort',$this->GetFields())) $result .= $sep."`sort` asc";
        return ($result!=''?" ORDER BY {$result} ":"");
    }
    /**
     * Фильтр по тексту поля
     * 
     * @param type $where
     * @param type $filter
     * @return type
     */
    protected function Filter($where, $first=true, $filter = null){
        $result = "";
        $prefix = $first?" WHERE ":" AND ";
        if($where!=null && $where!="") $prefix = $where." AND ";
        $sep="";
        if(is_array($filter)) foreach ($filter as $key => $value) if($this->FieldExists($key, $this->GetFields()) && $value!=null && $value!=""){
            $result .= $sep . "`{$key}` LIKE '" . $this->Escape($value) . "%'";
            $sep = " AND ";
        }
        //print_r(($result!="")?$prefix.$result.") ":$where); exit();
        return ($result!="")?$prefix.$result:$where;
    }    
    
    private function TranslateRow($row){
        foreach ($row as $field => $value) {
            if(in_array($field, $this->LngColumns)){
                $value = explode(',', $value);
                $result = [];
                foreach ($value as $code) {
                    $result[$code] = $this->Reg->Lng->TranslateSys($this->GetTableName().DIRECTORY_SEPARATOR.$field.DIRECTORY_SEPARATOR.$row['id'], $code);
                }
                $row[$field] = $result;
            }
        }
        return $row;
    }
    private function TranslateSave($field, $id, $arr){
        $result = [];      
        $code = $this->GetTableName().DIRECTORY_SEPARATOR.$field.DIRECTORY_SEPARATOR.$id;  
        foreach ($arr as $codelng => $value) {
            if(array_key_exists($codelng, $this->Languages)){
                $result[]=$codelng;
                $modelLng = new \Model\Lng\Dictionary($this->Reg, 'lng_sys_'.$codelng);
                $record = $modelLng->GetAll("`code` = '".$modelLng->Escape($code)."'");
                if($record->Count>0){
                    $modelLng->Save(['id'=>$record->Row['id'], 'item'=>['id'=>$record->Row['id'], 'code'=>$code, 'title'=>$value]]);
                } else $modelLng->Add(['item'=>['code'=>$code, 'title'=>$value]]);
            }
        }
        return $result;
    }
    
    private function _copy($id, $names, $name_prefix=null, $where = null){
        $fields = $this->GetFields();
        $head1 = "";
        $head2 = "";
        $sep = "";
        if(!isset($name_prefix)) $name_prefix=" (Копия)";
        foreach($fields as $field){
            if($field['name']!='id'){
                if(!in_array($field['name'], $names)){
                    $head1 .= $sep . "`{$field['name']}`";
                    $head2 .= $sep . "`{$field['name']}`";
                } else {
                    $head1 .= $sep . "`{$field['name']}`";
                    $head2 .= $sep . "CONCAT(`{$field['name']}`,'".$this->Escape($name_prefix)."') as `{$field['name']}`";
                }
                $sep = ",";
            }
        } 
        $ids = "`id`=".(int)$id;
        if(isset($where)) $ids .= " AND ({$where})";
        $sql = "INSERT INTO `".$this->GetTableName()."` ({$head1}) SELECT {$head2} FROM `".$this->GetTableName(). "` " . $this->Where($ids);
        $this->Reg->DB->Query($sql);
    }
}