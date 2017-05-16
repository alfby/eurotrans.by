<?php
function prt($obj){
    echo "<b>printAdmin:</b><pre>";
    var_dump($obj);
    echo "</pre>";
}
function getUniqueCitiesArray($arAddCities, $keyField, $arResult = array()){
    if(!empty($arAddCities)):
        foreach($arAddCities as $arAddCity):
            $arResult[$arAddCity[$keyField]] = array('id'=>$arAddCity[$keyField],'name'=>$arAddCity['name']);
        endforeach;
    endif;
    
    return $arResult;
}
function sortCallback($v1, $v2) {
    if ($v1['name'] == $v2['name']) return 0;
        return ($v1['name'] < $v2['name'])? -1: 1;
}
function getMinQuant(array $arBus,$field){
    $minSeats = $arBus[0][$field];
    foreach($arBus as $bus):
        if($bus[$field] < $minSeats):
            $minSeats = $bus[$field];
        endif;
    endforeach;
    
    return $minSeats;
}

function getTotTicketPrice(array $busList,$fieldPrice,$arTotPrice = false){
    $totalTicketPrice = array();
    foreach($busList['bus_arr'] as $bus){
        if(isset($bus['ticket_arr']) && !empty($bus['ticket_arr'])){
            foreach($bus['ticket_arr'] as $arTicket){
                if((false !== $arTotPrice) && isset($arTotPrice[$arTicket['ticket']])){
                    $totalTicketPrice[$arTicket['ticket']] = $arTotPrice[$arTicket['ticket']] + $arTicket[$fieldPrice];
                }else{
                    $totalTicketPrice[$arTicket['ticket']] = $arTicket[$fieldPrice];
                }
            }            
        }
    }
    return $totalTicketPrice;
}