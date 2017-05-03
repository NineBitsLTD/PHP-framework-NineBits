<?php

namespace Sys\Helper\Html;

interface FieldInterface{
    /**
     * Печать HTML контента соответственно формату данных
     * 
     * @param string $key Ключь столбца для которого выполняется печать
     * @param array $fields Параметры полей
     * @param array $item Запись из базы данных
     */
    public static function PrintField(& $reg, $key, $fields=[], $item=[], $class="");
}

