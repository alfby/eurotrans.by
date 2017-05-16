<?if(0 !== $minSeats){?>
    <?if(isset($bus_list['bus_arr'])){?>
        <?$bus_arr = $bus_list['bus_arr']?>
        <?$strIds = $dataPickupId.$dataReturnId?>
        <header class="pjBsFormHead clearfix">
            <p class="pjBsFormTitle pull-left">
                <?__('front_journey_from');?> 
                <strong><?=$bus_list['from_location']?></strong> 
                <?__('front_to');?>
                <strong><?=$bus_list['to_location']?></strong>
            </p>

            <dl class="dl-horizontal pjBsDepartureDate pull-right">
                <dt><?__('front_date_departure');?>: </dt>
                <dd>
                    <?if((isset($return_date['selected_date']) && ($date['selected_date'] != $return_date['selected_date'])) || $STORE['is_transfer'] == 1){?>
                        <a href="#" class="bsDateNav" data-type="return" data-pickup="<?=$pickup_id?>" data-return="<?=$return_id?>" data-date="<?=$date['current_date'];?>" data-is_transfer="<?=$STORE['is_transfer']; ?>" data-is_return="T" data-return_date="<?=$return_date['previous_date'];?>">&laquo;&nbsp;<?=__('front_prev');?></a>
                    <?}?>
                    <strong><?=$return_date['current_date']?></strong>
                    <a href="#" class="bsDateNav" data-type="return" data-pickup="<?=$pickup_id?>" data-return="<?=$return_id?>" data-date="<?=$date['current_date'];?>" data-is_transfer="<?=$STORE['is_transfer']; ?>" data-is_return="T" data-return_date="<?=$return_date['next_date'];?>"><?=__('front_next');?> &raquo;</a>
                </dd>
            </dl>
        </header>

        <div class="pjBsFormBody">
            <div class="panel panel-default pjBsSeats bsBusContainer" data-pickup_id="<?=$dataPickupId?>" data-return_id="<?=$dataReturnId?>">
                <?include $pathToViewElements.'journeyHeaders.php'?>
                <ul class="list-group pjBsListBusses">
                    <?foreach($bus_arr as $return_bus){ 
                        $seats_avail = $return_bus['seats_available'];
                        $location_arr = $return_bus['locations'];
                        ?>
                        <li class="list-group-item">
                            <div id="bsReturnRow_<?=$bus['id'];?>" class="row bsReturnRow bsReturnRow_<?=$bus['id'];?><?=isset($booked_data) && $booked_data['bus_id'] == $bus['id'] ? ' bsFocusRow' : null;?>">
                                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-8">
                                    <p class="clearfix pjBsBusTitle">
                                        <?=$return_bus['route'];?>
                                        <a href="#" class="pull-right pjBrDestinationTip" data-id="<?=$return_bus['id'];?>">
                                            <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                                        </a>
                                    </p>

                                    <div id="pjBrTipClone_<?=$return_bus['id'];?>" style="display: none;">
                                        <ul class="list-unstyled pjBsListTicks">
                                            <?foreach($location_arr as $location){?>
                                                <li><span class="glyphicon glyphicon-ok" aria-hidden="true"></span><?=$location['content'] . " - " . (!empty($location['departure_time']) ? pjUtil::formatTime($location['departure_time'], 'H:i:s', $tpl['option_arr']['o_time_format']) : pjUtil::formatTime($location['arrival_time'], 'H:i:s', $tpl['option_arr']['o_time_format']));?></li><?
                                            }?>
                                        </ul>
                                    </div>
                                </div>

                                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
                                    <p class="pjBsBusAvailableSeats"><?=$seats_avail;?></p>
                                    <input type="hidden" id="bs_return_avail_seats_<?=$return_bus['id'];?>" name="return_avail_seats" value="<?=join("~|~", $bus['seat_avail_arr']) ;?>"/>
                                    <input type="hidden" id="bs_return_number_of_seats_<?=$return_bus['id'];?>" class='bs_number_of_seats' value="<?=$seats_avail;?>"/>
                                </div>

                                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
                                    <p class="pjBsBusDate"><?=date($tpl['option_arr']['o_date_format'], strtotime($return_bus['departure_time'])) . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($return_bus['departure_time']));?></p><!-- /.pjBsBusDate -->
                                </div>

                                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
                                    <p class="pjBsBusDate"><?=date($tpl['option_arr']['o_date_format'], strtotime($return_bus['arrival_time'])) . ', ' . date($tpl['option_arr']['o_time_format'], strtotime($return_bus['arrival_time']));?></p><!-- /.pjBsBusDate -->
                                </div>

                                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-4">
                                    <p class="pjBsBusDate"><?=$return_bus['duration'];?></p>
                                </div>

                                <div class="col-lg-8 col-lg-offset-4 col-md-8 col-md-offset-4 col-sm-12 col-xs-12 <?=($showCommonPanel)?'hide':'';?>">
                                    <div class="row">
                                        <?$ticket_arr = $return_bus['ticket_arr'];
                                        for($i = 0; $i < $bus_list['ticket_columns']; $i++)
                                        {
                                            if(isset($ticket_arr[$i]))
                                            {
                                                $ticket = $ticket_arr[$i];
                                                if ($STORE['is_transfer'] == 1) {
                                                        $ticket['discount'] = 0;
                                                }
                                                $price = $ticket[$field_price] - ($ticket[$field_price] * $ticket['discount'] / 100);
                                                ?>
                                                <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                                                    <div class="form-group">
                                                        <label for=""><?=$ticket['ticket'];?></label>
                                                        <div class="input-group">
                                                            <select 
                                                                id="return_ticket_cnt_<?=$return_bus['id'] . '_' . $ticket['ticket_id'];?>"
                                                                data-ticket-type="<?=$ticket['ticket'];?>"
                                                                name="return_ticket_cnt_<?=$ticket['ticket_id'];?>" 
                                                                class="form-control bsReturnTicketSelect bsTicketSelect bsReturnTicketSelect-<?=$return_bus['id'];?>" 
                                                                data-set="<?=!empty($return_bus['seats_map']) ? 'T' : 'F';?>" 
                                                                data-pickup="<?=$bus['id']; ?>" 
                                                                data-pickup-bus="<?=$bus['id']; ?>" 
                                                                data-bus="<?=$return_bus['id']; ?>" 
                                                                data-pickup_id="<?=$dataPickupId?>" 
                                                                data-return_id="<?=$dataReturnId?>" 
                                                                data-price="<?=$ticket[$field_price];?>"
                                                            >
                                                            <?for($j = 0; $j <= $seats_avail; $j++)
                                                            {?>
                                                                <option value="<?=$j;?>"<?=isset($booked_data['return_ticket_cnt_' . $ticket['ticket_id']]) && $booked_data['return_ticket_cnt_' . $ticket['ticket_id']] == $j ? ' selected="selected"' : null;?>><?=$j; ?></option>
                                                            <?}?>
                                                            </select>

                                                            <span class="input-group-addon">x <?=pjUtil::formatCurrencySign(number_format($price, 2), $_SESSION[$controller->defaultFrontTicketCurrency]);?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?}
                                        }?>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?}?>
                </ul>
                <?
                if ((isset($STORE['is_return']) && $STORE['is_return'] == 'T') || (isset($STORE['is_transfer']) && (1 == isset($STORE['is_transfer'])))){
                    if(isset($booked_data)){
                        $selected_seats_arr = (is_array($booked_data['return_selected_seats'])) ? $booked_data['return_selected_seats'] : explode("|", $booked_data['return_selected_seats']);
                        $intersect = array_intersect($bus_list['booked_seat_arr'], $selected_seats_arr);
                    }?>

                    <div class="panel-body pjBsSeatsBody pjBsReturnSeatsBody" style="display:<?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
                        <div class="text-danger bsTicketErrorMsg bsReturnTicketErrorMsg" style="display: none;"><? __('front_validation_tickets');?></div>
                        <div class="pjBsListSeats">
                            <div class="pjBsChosenSeats" style="display:<?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
                                <p><? __('front_select');?> <strong id="bsReturnSeats_<?=$_GET['index'].$strIds;?>"><?=isset($booked_data) ? ( empty($intersect) ? ( $booked_data['return_selected_ticket'] > 0 ? ($booked_data['return_selected_ticket'] != 1 ? ($booked_data['return_selected_ticket'] . ' ' . pjSanitize::clean(__('front_seats', true, false))) : ($booked_data['return_selected_ticket'] . ' ' . pjSanitize::clean(__('front_seat', true, false))) ) :null) :null): null;?></strong></p>
                                <dl class="dl-horizontal" style="display: <?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'inline-block' : 'none';?>;">
                                    <dt><? __('front_selected_seats');?></dt>
                                    <dd id="bsReturnSelectedSeatsLabel_<?=$_GET['index'].$strIds;?>" class="bsSelectedSeatsLabel_<?=$_GET['index'].$strIds?> bsSelectedSeatsLabel"><?=isset($booked_data) ? ( empty($intersect) ? ($booked_data['return_selected_seats'] != '' ? join(", ", $bus_list['selected_seat_arr']) : null) : null ) : null;?></dd>
                                </dl>

                                <button type="button" id="bsReSelect<?=$strIds;?>" class="btn btn-link bsReturnReSelect bsReSelect" data-pickup_id=<?=$dataPickupId?> data-return_id=<?=$dataReturnId?> style="display:<?=isset($booked_data) ? (empty($intersect) ? ($booked_data['return_has_map'] == 'T' ? 'inline-block' : 'none') :'none' ) : 'none';?>"><? __('front_reselect');?></button>
                            </div>
                            <div id="bsReturnMapContainer_<?=$_GET['index'].$strIds?>" class="bsMapContainer bsMapContainer_<?=$_GET['index'].$strIds?>" data-pickup_id="<?=$dataPickupId?>" data-return_id="<?=$dataReturnId?>" style="display:<?=isset($booked_data) && $booked_data['return_has_map'] == 'T' ? 'block' : 'none';?>;">
                                <?if(isset($booked_data) && $booked_data['return_has_map'] == 'T'){
                                    include PJ_VIEWS_PATH . 'pjFrontEnd/pjActionGetReturnSeats.php';
                                }?>
                            </div>
                        </div>
                        <div class="text-danger bsSeatErrorMsg bsReturnSeatErrorMsg"></div>
                    </div>
                <?}?>

                <footer class="panel-footer pjBsSeatsFoot pjBsReturnSeatsFoot" style="display:<?=isset($booked_data) && $booked_data['selected_ticket'] > 0 ? 'block' : 'none';?>;">
                    <?include $pathToViewElements.'listOfSeatTypes.php'?>
                </footer>

                <input type="hidden" id="bs_return_selected_tickets_<?=$_GET['index'].$strIds;?>" class="bs_return_selected_tickets bs_selected_tickets" name="return_selected_ticket" value="<?=isset($booked_data) && $booked_data['return_selected_ticket'] > 0 ? $booked_data['return_selected_ticket'] : null;?>" data-map="<?=isset($booked_data) && $booked_data['return_selected_ticket'] > 0 ? (!empty($bus_arr['bus_type_arr']['seats_map']) ? 'T' : 'F') : 'F';?>"/>
                <input type="hidden" id="bs_return_selected_seats_<?=$_GET['index'].$strIds?>" class="bs_selected_seats_<?=$_GET['index'].$strIds?> bs_selected_seats" name="return_selected_seats[<?=$strIds?>]" value="<?=isset($booked_data) && $booked_data['return_selected_seats'] != '' ? $booked_data['return_selected_seats'] : null;?>"/>
                <input type="hidden" id="bs_return_selected_bus_<?=$_GET['index'].$strIds;?>" class='bs_return_selected_bus bs_selected_bus' name="return_bus_id[<?=$strIds?>]" value="<?=isset($booked_data) && $booked_data['return_bus_id'][$strIds] != '' ? $booked_data['return_bus_id'][$strIds] : null;?>"/>
                <input type="hidden" id="bs_return_has_map_<?=$_GET['index'].$strIds;?>" name="return_has_map" value="<?=isset($booked_data) ? $booked_data['return_has_map'] : null;?>"/>
            </div>	
        </div>
    <?}else{?>

    <?}?>
<?}else{
    include $pathToViewElements.'ticketsNotAvailable.php';
}?>