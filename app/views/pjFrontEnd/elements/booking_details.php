<?
$currency = $_SESSION[$controller->defaultFrontTicketCurrency];
$field_price = 'price';
if (isset($controller->defaultTicketCurrencies[$currency])) {
	$field_price = $controller->defaultTicketCurrencies[$currency];
}
$pathToFrontEndElements = PJ_VIEWS_PATH . 'pjFrontEnd/elements/';

$sub_total = $tpl['ticket_price_total']['sub_total'];
$tax = $tpl['ticket_price_total']['tax'];
$total = $tpl['ticket_price_total']['total'];
$deposit = $tpl['ticket_price_total']['deposit'];

$return_sub_total = isset($tpl['return_price_arr']['sub_total']) ? $tpl['return_price_arr']['sub_total'] : 0;
$return_tax = isset($tpl['return_price_arr']['tax']) ? $tpl['return_price_arr']['tax'] : 0;
$return_total = isset($tpl['return_price_arr']['total']) ? $tpl['return_price_arr']['total'] : 0;
$return_deposit = isset($tpl['return_price_arr']['deposit']) ? $tpl['return_price_arr']['deposit'] : 0;
?>
<header class="pjBsFormHead">
    <p class="pjBsFormTitle"><? __('front_booking_details');?></p>

    <div class="row pjBsFormBoxes">
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6 pjBsFormBox">
            <div class="pjBsFormBoxInner">
                <p class="pjBsFormBoxTitle"><? __('front_journey');?></p>
                <?if ('T' == $STORE['is_return']){?>
                    <?if (1 == $STORE['is_transfer']){
                        if(isset($tpl['before_transfer_bus_arr'])){
                            $bus_arr = $tpl['before_transfer_bus_arr'];
                            include $pathToFrontEndElements.'direct_bus_data.php';
                        }
                        if(isset($tpl['after_transfer_bus_arr'])){?>
                            <dl class="dl-horizontal pjBsFormBoxData">
                                <dt class="transfer-lbl"><? __('front_label_transfer');?>: </dt>
                            </dl>  
                
                            <?$bus_arr = $tpl['after_transfer_bus_arr'];
                            include $pathToFrontEndElements.'direct_bus_data.php';
                        }?>
                        
                            <hr>
                            <p class="pjBsFormBoxTitle"><? __('front_return_journey');?></p>
                            <hr>
                        
                        <?if(isset($tpl['return_before_transfer_bus_arr'])){
                            $bus_arr = $tpl['return_before_transfer_bus_arr'];
                            include $pathToFrontEndElements.'direct_bus_data.php';
                        }?>
                        
                         <?if(isset($tpl['return_after_transfer_bus_arr'])){?>
                            <dl class="dl-horizontal pjBsFormBoxData">
                                <dt class="transfer-lbl"><? __('front_label_transfer');?>: </dt>
                            </dl>  
                
                            <?$bus_arr = $tpl['return_after_transfer_bus_arr'];
                            include $pathToFrontEndElements.'direct_bus_data.php';
                        } ?>   
                            
                            
                    <?}else{
                        if(isset($tpl['bus_arr'])){
                            $bus_arr = $tpl['bus_arr'];
                            include $pathToFrontEndElements.'direct_bus_data.php';
                        }

                        if(isset($tpl['return_bus_arr'])){?>
                            <hr>
                            <p class="pjBsFormBoxTitle"><? __('front_return_journey');?></p>
                            
                            <?$bus_arr = $tpl['return_bus_arr'];
                            include $pathToFrontEndElements.'direct_bus_data.php';
                        }
                    }?>
                <?}else{?>
                    <?if (1 == $STORE['is_transfer']){
                        if(isset($tpl['before_transfer_bus_arr'])){
                            $bus_arr = $tpl['before_transfer_bus_arr'];
                            include $pathToFrontEndElements.'direct_bus_data.php';
                        }?>
                        
                        <?if(isset($tpl['after_transfer_bus_arr'])){?>
                            <dl class="dl-horizontal pjBsFormBoxData">
                                <dt class="transfer-lbl"><? __('front_label_transfer');?>: </dt>
                            </dl>
                            
                            <?$bus_arr = $tpl['after_transfer_bus_arr'];
                            include $pathToFrontEndElements.'direct_bus_data.php'; 
                        }
                    }else{//direct way
                        $bus_arr = $tpl['bus_arr'];
                        include $pathToFrontEndElements.'direct_bus_data.php';
                    }?>
                <?}?>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6 pjBsFormBox">
            <div class="pjBsFormBoxInner">
                <p class="pjBsFormBoxTitle"><? __('front_tickets');?></p>
                <?include $pathToFrontEndElements.'tickets.php'?>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6 pjBsFormBox">
            <div class="pjBsFormBoxInner">
                <p class="pjBsFormBoxTitle"><? __('front_payment');?></p>
                <dl class="dl-horizontal pjBsFormBoxData">
                    <dt><? __('front_tickets_total');?></dt>
                    <dd><?=pjUtil::formatCurrencySign(number_format($sub_total + $return_sub_total, 2), $currency);?></dd>
                </dl>
                <dl class="dl-horizontal pjBsFormBoxData">
                    <dt><? __('front_total');?></dt>
                    <dd><?=pjUtil::formatCurrencySign(number_format($total + $return_total, 2), $currency);?></dd>
                </dl>
            </div>
        </div>
    </div>
</header>

<input type="hidden" name="sub_total" value="<?=$sub_total;?>" />
<input type="hidden" name="tax" value="<?=$tax;?>" />
<input type="hidden" name="total" value="<?=$total;?>" />
<input type="hidden" name="deposit" value="<?=$deposit;?>" />
<input type="hidden" name="return_sub_total" value="<?=$return_sub_total;?>" />
<input type="hidden" name="return_tax" value="<?=$return_tax;?>" />
<input type="hidden" name="return_total" value="<?=$return_total;?>" />
<input type="hidden" name="return_deposit" value="<?=$return_deposit?>" />