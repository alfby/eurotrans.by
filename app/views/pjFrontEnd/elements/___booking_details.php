<?
$currency = $_SESSION[$controller->defaultFrontTicketCurrency];
$field_price = 'price';
if (isset($controller->defaultTicketCurrencies[$currency])) {
	$field_price = $controller->defaultTicketCurrencies[$currency];
}

$sub_total = $tpl['price_arr']['sub_total'];
$tax = $tpl['price_arr']['tax'];
$total = $tpl['price_arr']['total'];
$deposit = $tpl['price_arr']['deposit'];

$return_sub_total = isset($tpl['return_price_arr']['sub_total']) ? $tpl['return_price_arr']['sub_total'] : 0;
$return_tax = isset($tpl['return_price_arr']['tax']) ? $tpl['return_price_arr']['tax'] : 0;
$return_total = isset($tpl['return_price_arr']['total']) ? $tpl['return_price_arr']['total'] : 0;
$return_deposit = isset($tpl['return_price_arr']['deposit']) ? $tpl['return_price_arr']['deposit'] : 0;
?>
<header class="pjBsFormHead">
    <p class="pjBsFormTitle"><? __('front_booking_details');?></p><!-- /.pjBsFormTitle -->

    <div class="row pjBsFormBoxes">
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6 pjBsFormBox">
            <div class="pjBsFormBoxInner">
                <p class="pjBsFormBoxTitle"><? __('front_journey');?></p><!-- /.pjBsFormBoxTitle -->

                <dl class="dl-horizontal pjBsFormBoxData">
                    <dt><? __('front_date');?>: </dt>
                    <dd><?=$STORE['date'];?> <a href="#" class="btn btn-link bsChangeDate"><? __('front_link_change_date');?></a></dd>
                </dl>

                <dl class="dl-horizontal pjBsFormBoxData">
                    <dt><? __('front_departure_from');?>: </dt>
                    <dd><?=$tpl['from_location']?> <? __('front_at');?> <?=$tpl['bus_arr']['departure_time'];?></dd>
                </dl>

                <dl class="dl-horizontal pjBsFormBoxData">
                    <dt><? __('front_arrive_to');?>: </dt>
                    <dd><?=$tpl['to_location']?> <? __('front_at');?> <?=$tpl['bus_arr']['arrival_time'];?></dd>
                </dl>

                <dl class="dl-horizontal pjBsFormBoxData">
                    <dt><? __('front_bus');?>: </dt>
                    <dd><?=$tpl['bus_arr']['route_title'];?></dd>
                </dl>
                <?
                if (isset($tpl['is_return']) && $tpl['is_return'] == 'T')
                {?>
                    <hr/>
                    <? if ($STORE['is_transfer'] == 1) { ?>
                        <dl class="dl-horizontal pjBsFormBoxData">
                            <dt><? __('front_label_transfer');?>: </dt>
                        </dl>
                    <? } else { ?>
                        <dl class="dl-horizontal pjBsFormBoxData">
                            <dt><? __('front_return_date');?>: </dt>
                            <dd><?=$STORE['return_date'];?> <a href="#" class="btn btn-link bsChangeDate"><? __('front_link_change_date');?></a></dd>
                        </dl>
                    <? } ?>
                    <dl class="dl-horizontal pjBsFormBoxData">
                        <dt><? __('front_departure_from');?>: </dt>
                        <dd><?=$tpl['return_from_location']?> <? __('front_at');?> <?=$tpl['return_bus_arr']['departure_time'];?></dd>
                    </dl>

                    <dl class="dl-horizontal pjBsFormBoxData">
                        <dt><? __('front_arrive_to');?>: </dt>
                        <dd><?=$tpl['return_to_location']?> <? __('front_at');?> <?=$tpl['return_bus_arr']['arrival_time'];?></dd>
                    </dl>

                    <dl class="dl-horizontal pjBsFormBoxData">
                        <dt><? __('front_bus');?>: </dt>
                        <dd><?=$tpl['return_bus_arr']['route_title'];?></dd>
                    </dl>
                <?}?>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6 pjBsFormBox">
            <div class="pjBsFormBoxInner">
                <p class="pjBsFormBoxTitle"><? __('front_tickets');?></p><!-- /.pjBsFormBoxTitle -->

                <dl class="dl-horizontal pjBsFormBoxData">
                    <dt><? __('front_tickets');?>: </dt>

                    <dd>
                        <?foreach($tpl['ticket_arr'] as $k => $v)
                        {
                            if(isset($booked_data['ticket_cnt_' . $v['ticket_id']]) && $booked_data['ticket_cnt_' . $v['ticket_id']] > 0)
                            {?>
                                <p><?=$booked_data['ticket_cnt_' . $v['ticket_id']];?> <?=$v['ticket'];?> x <?=pjUtil::formatCurrencySign($v[$field_price], $currency);?></p>
                            <?}
                        }
                        if (isset($tpl['return_ticket_arr']))
                        {
                            foreach($tpl['return_ticket_arr'] as $k => $v)
                            {
                                if(isset($booked_data['return_ticket_cnt_' . $v['ticket_id']]) && $booked_data['return_ticket_cnt_' . $v['ticket_id']] > 0)
                                {
                                    if ($STORE['is_transfer'] == 1) {
                                        $v['discount'] = 0;
                                    }
                                    $price = $v[$field_price] - ($v[$field_price] * $v['discount'] / 100);
                                    ?><p><?=$booked_data['return_ticket_cnt_' . $v['ticket_id']];?> <?=$v['ticket'];?> x <?=pjUtil::formatCurrencySign(number_format($price, 2), $currency);?></p><?
                                }
                            }
                        }?>
                        <p><a href="#" class="btn btn-link bsChangeSeat"><? __('front_link_change_seats');?></a></p>
                    </dd>
                </dl>
                <?if(!empty($tpl['selected_seat_arr']))
                {?>								
                    <dl class="dl-horizontal pjBsFormBoxData">
                        <dt><?=ucfirst(__('front_seats', true, false));?>: </dt>
                        <dd><?=join(", ", $tpl['selected_seat_arr']);?></dd>
                    </dl>
                <?}
                if(!empty($tpl['return_selected_seat_arr']))
                {?>
                    <dl class="dl-horizontal pjBsFormBoxData">
                        <? if ($STORE['is_transfer'] == 1) {?>
                            <dt><?=ucfirst(__('front_transfer_seats', true, false));?>: </dt>
                        <? } else { ?>
                            <dt><?=ucfirst(__('front_return_seats', true, false));?>: </dt>
                        <? } ?>
                        <dd><?=join(", ", $tpl['return_selected_seat_arr']);?></dd>
                    </dl>
                <?}?>
            </div>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-6 pjBsFormBox">
            <div class="pjBsFormBoxInner">
                <p class="pjBsFormBoxTitle"><? __('front_payment');?></p><!-- /.pjBsFormBoxTitle -->

                <dl class="dl-horizontal pjBsFormBoxData">
                    <dt><? __('front_tickets_total');?></dt>
                    <dd><?=pjUtil::formatCurrencySign(number_format($sub_total + $return_sub_total, 2), $currency);?></dd>
                </dl><!-- /.dl-horizontal pjBsFormBoxData -->
                <? /*								
                <dl class="dl-horizontal pjBsFormBoxData">
                    <dt><? __('front_tax');?></dt>
                    <dd><?=pjUtil::formatCurrencySign(number_format($tax + $return_tax, 2), $currency);?></dd>
                </dl><!-- /.dl-horizontal pjBsFormBoxData -->
                */?>								
                <dl class="dl-horizontal pjBsFormBoxData">
                    <dt><? __('front_total');?></dt>
                    <dd><?=pjUtil::formatCurrencySign(number_format($total + $return_total, 2), $currency);?></dd>
                </dl><!-- /.dl-horizontal pjBsFormBoxData -->
                <? /*								
                <dl class="dl-horizontal pjBsFormBoxData">
                    <dt><? __('front_deposit');?></dt>
                    <dd><?=pjUtil::formatCurrencySign(number_format($deposit + $return_deposit, 2), $currency);?></dd>
                </dl><!-- /.dl-horizontal pjBsFormBoxData -->
                */?>
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
<input type="hidden" name="return_deposit" value="<?=$return_deposit;?>" />