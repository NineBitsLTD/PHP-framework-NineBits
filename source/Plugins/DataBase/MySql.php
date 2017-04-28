<?php

namespace Sys\DataBase;

/**
 * Провайдер базы данных MySql
 * 
 * Данный класс осуществляет доступ к базе данных средствами выбранного провайдера.
 * 
 */
class MySql extends \Sys\DataBase\Provider
{ 
    /**
     * Перечень текстов известных ошибок
     * 
     * @var Array
     */
    public $Errors = [
        'Failed to create a database connection, an incorrect password, or one or more other parameters.'
    ];
    /**
     * Ссылка на базу данных
     * 
     * @var \PDO
     */
    protected $Connection = null;
    /**
     * Представляет подготовленное заявление, и, после того, как выполняется оператор, соответствующий результирующий набор.
     * 
     * @var \PDOStatement 
     */
    protected $Statement = null;
    
    /**
     * Подключение к базе данных MySql
     * 
     * @param \Sys\Config\DataBase $config Конфигурация базы данных
     */
    public function __construct($config) {
        parent::__construct($config);        
    }
    function __destruct() {
        $this->Connection = null;
        $this->Statement = null;
    }

    /**
     * Подключение к базе данных
     */
    public function Connect() {
        try {            
            $this->Connection = new \PDO("mysql:host=" . $this->Host . ";port=" . $this->Port . ";dbname=" . $this->DBName, $this->User, $this->Password, array(\PDO::ATTR_PERSISTENT => true));
            if($this->Connection!=null){
                $this->Connection->exec("SET NAMES '{$this->Charset}'");
                $this->Connection->exec("SET CHARACTER SET {$this->Charset}");
                $this->Connection->exec("SET CHARACTER_SET_CONNECTION={$this->Charset}");
                $this->Connection->exec("SET SQL_MODE = ''");
            } else {
                throw new \Exception($this->Errors[0]); exit();
            }
        } catch(\PDOException $e) {            
            throw $e; exit();
        }
    }
    /**
     * Количество затронутых записей
     */
    public function CountAffected() {
        if ($this->Statement) {
            return $this->Statement->rowCount();
        } else {
            return 0;
        }
    }
    /**
     * Экранирование данных
     *  
     * @param string $value Входная строка для экранирования
     * @return string Экранированные данные
     */
    public function Escape($value) {
        return \Sys\Helper::$Security->EscapeEncodeSql($value);
    }
    /**
     * Получение последнего индекса записи после запроса INSERT
     * 
     * @return string
     */
    public function GetLastId() {
        return $this->Connection->lastInsertId();
    }
    /**
     * Выполнение запроса к базе данных
     * 
     * @param string $sql Строка запроса
     * @param array $params Параметры запроса PDO
     * @return \Sys\DataBase\ActiveRecord
     * @throws \PDOException
     */
    public function Query($sql, $params = array()) {
        $this->Statement = $this->Connection->prepare($sql);
        $result = new \Sys\DataBase\ActiveRecord($this->Registry, $this);

        try {
            if ($this->Statement && $this->Statement->execute($params)) {
                $data = array();
                while ($row = $this->Statement->fetch(\PDO::FETCH_ASSOC)) {
                    $data[] = $row;
                }
                $result->Row = (isset($data[0]) ? $data[0] : array());
                $result->Rows = $data;
                $result->Count = $this->Statement->rowCount();
            }
        } catch (\PDOException $e) {
            throw $e; exit();
        }
        return $result;
    }

}
