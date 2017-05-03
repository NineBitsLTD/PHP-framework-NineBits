<?php
namespace Helper;

/**
 * 
 */
class Date
{
    /**
     * Разница между текущей и указаннойдатой
     * 
     * @param string $date Начальная дата
     * @return int Разница в минутах
     */
    public function CurrentDiff($date)
    {
        $result = 0;
        $dat1 = new \DateTime($date);
        $dat2 = new \DateTime(date("Y-m-d H:i:s",time()));
        if($dat1<$dat2){
            $diff = $dat1->diff($dat2);
            $result = (int)$diff->format("%a")*24*60+(int)$diff->format("%h")*60+(int)$diff->format("%i");
        }
        return $result;
    }
    /**
     * Отобразить интервал в виде текста
     * 
     * @param \Sys\Registry $reg
     * @param int $minutes
     * @return string
     */
    public function MinToText($reg, $minutes){
        if($minutes<60) return $minutes." ".$reg->Translate("Minutes");
        if($minutes<60*24) return (int)($minutes/60)." ".$reg->Translate("Hours");
        return (int)($minutes/60/24)." ".$reg->Translate("Days");
    }
}