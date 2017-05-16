<div id='commonTicketPanel' class="col-lg-8 col-lg-offset-4 col-md-10 col-md-offset-2 col-sm-12 col-xs-12">
    <div class="row">
    <?for($i = 0; $i < $tpl['ticket_columns']; $i++)
    {
        if(isset($ticket_arr[$i]))
        {
            $ticket = $ticket_arr[$i];
            $field_price = 'price';
            if (isset($_SESSION[$controller->defaultFrontTicketCurrency])) {
                $field_price = $controller->defaultTicketCurrencies[$_SESSION[$controller->defaultFrontTicketCurrency]];	
            }
            
            if($ticket[$field_price] != '')
            {?>
                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
                    <div class="form-group common-price">
                        <label for=""><?=$ticket['ticket'];?></label>
                        <div class="input-group">
                            <select
                                name="ticket_cnt_<?=$ticket['ticket_id'];?>"
                                data-ticket-type="<?=$ticket['ticket']?>"
                                class="form-control bsTicketExchangeSelect"
                            >
                                <?for($j = 0; $j <= $tpl['min_seats'];$j++){?>
                                <option value="<?=$j;?>"<?=isset($booked_data) && $booked_data['ticket_cnt_' . $ticket['ticket_id']] == $j ? ' selected="selected"' : null;?>><?=$j;?></option>
                                <?}?>
                            </select>
                            <span class="input-group-addon">x <?=pjUtil::formatCurrencySign($tpl['total_ticket_price_arr'][$ticket['ticket']], $_SESSION[$controller->defaultFrontTicketCurrency]);?></span>
                        </div><!-- /.input-group -->
                    </div><!-- /.form-group -->
                </div>
            <?}
        }
    }?>
    </div><!-- /.row -->
</div>