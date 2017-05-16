<dl class="dl-horizontal pjBsFormBoxData">
    <dt><? __('front_tickets');?>: </dt>
    <dd>
        <?foreach($tpl['ticket_price_arr'] as $ticketPrice){
            foreach($ticketPrice['ticket_arr'] as $k => $v)
            {
                if(isset($booked_data['ticket_cnt_' . $v['ticket_id']]) && $booked_data['ticket_cnt_' . $v['ticket_id']] > 0)
                {?>
                    <p><?=$booked_data['ticket_cnt_' . $v['ticket_id']];?> <?=$v['ticket'];?> x <?=pjUtil::formatCurrencySign($v[$field_price], $currency);?></p>
                <?}
            }?>    
        <?}?>
    </dd>  
    
    <?if (isset($tpl['return_ticket_price_arr'])){?>
        <dt><? __('front_return_tickets');?>: </dt>
        <dd>
            <?foreach($tpl['return_ticket_price_arr'] as $returnTicketPrice) {
            foreach($returnTicketPrice['ticket_arr'] as $k => $v)
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
        </dd>
    <?}?>
    <p><a href="#" class="btn btn-link bsChangeSeat"><? __('front_link_change_seats');?></a></p>
</dl>

<hr>
<p class="pjBsFormBoxTitle"><? __('front_seats');?></p>
<?if(!empty($tpl['selected_seat_arr'])){?>
    <?foreach($tpl['selected_seat_arr'] as $seats_arr){?>
    <dl class="dl-horizontal pjBsFormBoxData">
        <dt><?=ucfirst(__('front_seats', true, false));?>: </dt>
        <dd><?=join(", ", $seats_arr);?></dd>
    </dl>
    <?}?>
<?}
if(!empty($tpl['return_selected_seat_arr'])){?>
    <?foreach($tpl['return_selected_seat_arr'] as $return_seats_arr){?>
    <dl class="dl-horizontal pjBsFormBoxData">
        <dt><?=ucfirst(__('front_return_seats', true, false));?>: </dt>
        <dd><?=join(", ", $return_seats_arr);?></dd>
    </dl>
    <?}?>
<?}?>
